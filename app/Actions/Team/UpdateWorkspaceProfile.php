<?php

declare(strict_types=1);

namespace App\Actions\Team;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;

final readonly class UpdateWorkspaceProfile
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, Team $team, array $data): Team
    {
        Gate::forUser($user)->authorize('update', $team);

        $team->update(Arr::only($data, [
            'partita_iva',
            'address',
            'city',
            'postal_code',
            'province',
            'country',
            'phone',
            'email',
        ]));

        return $team;
    }
}
