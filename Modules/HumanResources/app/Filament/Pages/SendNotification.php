<?php

namespace Modules\HumanResources\Filament\Pages;

use App\Enums\CourseYear;
use App\Enums\Role;
use App\Models\Course;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;
use Modules\HumanResources\Actions\SendBroadcast;
use UnitEnum;

/**
 * Invio di una notifica a molti utenti insieme (HR 2.2.4): arriva in piattaforma a tutti i
 * destinatari e per mail a chi non ha disattivato le email del servizio HR.
 * TODO: allegati, quando ci sarà la pipeline di caricamento file con antivirus (CLAUDE.md).
 *
 * @property-read Schema $form
 */
class SendNotification extends Page
{
    protected static ?string $slug = 'invia-notifica';

    protected static ?string $navigationLabel = 'Invia notifica';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|UnitEnum|null $navigationGroup = 'Comunicazioni';

    protected static ?int $navigationSort = 1;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return Gate::allows('broadcast', User::class);
    }

    public function getTitle(): string
    {
        return 'Invia una notifica';
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Messaggio')->schema([
                    TextInput::make('subject')->label('Titolo')->required()->maxLength(150),
                    Textarea::make('body')->label('Testo')->required()->rows(8)
                        ->helperText('Separa i paragrafi con una riga vuota.'),
                ]),
                Section::make('Destinatari')
                    ->description('Solo utenti attivi. Lascia vuoto un filtro per non restringere.')
                    ->columns(3)
                    ->schema([
                        CheckboxList::make('roles')->label('Ruoli')
                            ->options(collect(Role::cases())->mapWithKeys(fn (Role $role): array => [$role->value => $role->getLabel()])),
                        Select::make('courses')->label('Corsi')->multiple()->searchable()
                            ->options(fn (): array => Course::query()->orderBy('name')->pluck('name', 'id')->all()),
                        Select::make('course_years')->label('Anni di corso')->multiple()->options(CourseYear::class),
                        Text::make(fn (): string => 'Destinatari attuali: '.$this->recipientsCount())->columnSpanFull(),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('send')
                ->footer([
                    Actions::make([
                        Action::make('send')->label('Invia')->icon('heroicon-o-paper-airplane')->submit('send'),
                    ]),
                ]),
        ]);
    }

    public function send(): void
    {
        $data = $this->form->getState();

        /** @var User $admin */
        $admin = Filament::auth()->user();

        $count = app(SendBroadcast::class)->handle($data['subject'], $data['body'], $this->audience($data), $admin);

        Notification::make()->title("Notifica inviata a {$count} utenti")->success()->send();

        $this->form->fill();
    }

    private function recipientsCount(): int
    {
        return app(SendBroadcast::class)->recipients($this->audience($this->data ?? []))->count();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{roles: list<string>, courses: list<string>, course_years: list<string>}
     */
    private function audience(array $data): array
    {
        $values = fn (string $key): array => array_values(array_map(
            fn (mixed $value): string => $value instanceof BackedEnum ? (string) $value->value : (string) $value,
            $data[$key] ?? [],
        ));

        return ['roles' => $values('roles'), 'courses' => $values('courses'), 'course_years' => $values('course_years')];
    }
}
