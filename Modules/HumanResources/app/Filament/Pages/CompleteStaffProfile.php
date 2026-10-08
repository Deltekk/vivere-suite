<?php

namespace Modules\HumanResources\Filament\Pages;

use App\Models\StaffProfile;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\HumanResources\Filament\Schemas\UserFields;

/**
 * Dati richiesti solo a staff e amministratori (HR 2.2.1): codice fiscale, luogo di nascita
 * e scuola di provenienza (per Orientamento). Chi diventa staff viene portato qui finché
 * non li inserisce (vedi EnsureAccountIsReady).
 *
 * @property-read Schema $form
 */
class CompleteStaffProfile extends Page
{
    protected static ?string $slug = 'profilo-staff';

    protected static bool $shouldRegisterNavigation = false;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function getTitle(): string
    {
        return 'Completa il profilo staff';
    }

    public function mount(): void
    {
        $user = $this->user();

        $this->form->fill([
            ...($user->staffProfile?->only(['tax_code', 'birth_city', 'birth_province', 'birth_country']) ?? ['birth_country' => 'Italia']),
            'school_id' => $user->school_id,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->model($this->user())
            ->components([
                Section::make('Dati per lo staff')
                    ->description('Come membro dello staff ci servono questi dati. Li vedono solo gli amministratori.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('tax_code')->label('Codice fiscale')->required()->length(16)
                            ->regex('/^[A-Za-z0-9]{16}$/')
                            ->unique(StaffProfile::class, 'tax_code', ignorable: $this->user()->staffProfile),
                        TextInput::make('birth_city')->label('Città di nascita')->required()->maxLength(255),
                        TextInput::make('birth_province')->label('Provincia di nascita')->required()->maxLength(255),
                        TextInput::make('birth_country')->label('Stato di nascita')->required()->maxLength(255),
                        UserFields::school(required: true)->helperText('Obbligatoria per lo staff: serve alla piattaforma Orientamento.'),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label('Salva')->submit('save'),
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $user = $this->user();

        $user->staffProfile()->updateOrCreate([], [
            'tax_code' => strtoupper($data['tax_code']),
            'birth_city' => $data['birth_city'],
            'birth_province' => $data['birth_province'],
            'birth_country' => $data['birth_country'],
        ]);
        $user->school_id = $data['school_id'];
        $user->save();

        Notification::make()->title('Profilo staff completato')->success()->send();

        $this->redirect(Filament::getPanel('hr')->getUrl());
    }

    private function user(): User
    {
        /** @var User */
        return Filament::auth()->user();
    }
}
