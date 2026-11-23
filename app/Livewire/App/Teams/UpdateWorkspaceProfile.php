<?php

declare(strict_types=1);

namespace App\Livewire\App\Teams;

use App\Actions\Team\UpdateWorkspaceProfile as UpdateWorkspaceProfileAction;
use App\Livewire\BaseLivewireComponent;
use App\Models\Team;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;

final class UpdateWorkspaceProfile extends BaseLivewireComponent
{
    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public Team $team;

    public function mount(Team $team): void
    {
        $this->team = $team;

        $this->form->fill($team->only([
            'partita_iva',
            'address',
            'city',
            'postal_code',
            'province',
            'country',
            'phone',
            'email',
        ]));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('teams.sections.workspace_profile.title'))
                    ->aside()
                    ->description(__('teams.sections.workspace_profile.description'))
                    ->schema([
                        TextInput::make('partita_iva')
                            ->label(__('teams.form.partita_iva.label'))
                            ->string()
                            ->maxLength(20),
                        TextInput::make('address')
                            ->label(__('teams.form.address.label'))
                            ->string()
                            ->maxLength(255),
                        TextInput::make('city')
                            ->label(__('teams.form.city.label'))
                            ->string()
                            ->maxLength(255),
                        TextInput::make('postal_code')
                            ->label(__('teams.form.postal_code.label'))
                            ->string()
                            ->maxLength(10),
                        TextInput::make('province')
                            ->label(__('teams.form.province.label'))
                            ->string()
                            ->maxLength(2),
                        TextInput::make('country')
                            ->label(__('teams.form.country.label'))
                            ->string()
                            ->maxLength(2),
                        TextInput::make('phone')
                            ->label(__('teams.form.phone.label'))
                            ->tel()
                            ->maxLength(30),
                        TextInput::make('email')
                            ->label(__('teams.form.email.label'))
                            ->email()
                            ->nullable()
                            ->maxLength(255),
                        Actions::make([
                            Action::make('save')
                                ->label(__('teams.actions.save'))
                                ->action(fn () => $this->updateWorkspaceProfile($this->team)),
                        ])->alignEnd(),
                    ]),
            ])
            ->statePath('data');
    }

    public function updateWorkspaceProfile(Team $team): void
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->sendRateLimitedNotification($exception);

            return;
        }

        $data = $this->form->getState();

        resolve(UpdateWorkspaceProfileAction::class)->execute($this->authUser(), $team, $data);

        $this->sendNotification('teams.notifications.save.success');
    }

    public function render(): View
    {
        return view('livewire.app.teams.update-workspace-profile');
    }
}
