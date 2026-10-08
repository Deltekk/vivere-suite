<?php

namespace Modules\HumanResources\Filament\Pages;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\HumanResources\Filament\Schemas\UserFields;

/**
 * Conferma di ottobre dell'anno frequentato (HR 2.2.1): a inizio anno accademico tutti passano
 * all'anno successivo in automatico; durante ottobre al primo accesso si chiede di confermare
 * (o correggere) anno e corso, così il DB resta veritiero.
 *
 * @property-read Schema $form
 */
class ConfirmCourseYear extends Page
{
    protected static ?string $slug = 'conferma-anno';

    protected static bool $shouldRegisterNavigation = false;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function getTitle(): string
    {
        return 'Conferma il tuo anno di corso';
    }

    public function mount(): void
    {
        $this->form->fill($this->user()->only(['course_year', 'course_id']));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->model($this->user())
            ->components([
                Section::make('È iniziato un nuovo anno accademico')
                    ->description('Abbiamo aggiornato in automatico il tuo anno di corso: controlla che sia giusto (per esempio se ti sei laureato/a o hai cambiato corso).')
                    ->columns(2)
                    ->schema([
                        UserFields::courseYear(),
                        UserFields::course(),
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
                        Action::make('save')->label('Confermo')->submit('save'),
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $user = $this->user();

        $user->course_year = $data['course_year'];
        $user->course_id = $data['course_id'] ?? null;
        $user->course_year_confirmed_at = now();
        $user->save();

        Notification::make()->title('Grazie, anno di corso confermato')->success()->send();

        $this->redirect(Filament::getPanel('hr')->getUrl());
    }

    private function user(): User
    {
        /** @var User */
        return Filament::auth()->user();
    }
}
