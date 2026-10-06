<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\EmailCampaign;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final readonly class EmailCampaignPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasVerifiedEmail() && $user->currentTeam !== null;
    }

    public function view(User $user, EmailCampaign $campaign): bool
    {
        return $user->belongsToTeamId($campaign->team_id);
    }

    public function create(User $user): bool
    {
        return $user->hasVerifiedEmail() && $user->currentTeam !== null;
    }

    public function update(User $user, EmailCampaign $campaign): bool
    {
        return $user->belongsToTeamId($campaign->team_id);
    }

    public function delete(User $user, EmailCampaign $campaign): bool
    {
        return $user->belongsToTeamId($campaign->team_id);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasVerifiedEmail() && $user->currentTeam !== null;
    }

    public function send(User $user, EmailCampaign $campaign): bool
    {
        return $user->belongsToTeamId($campaign->team_id);
    }
}
