<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\Company;
use App\Models\User;
use App\Rules\ValidCustomFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateCompanyRequest extends FormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->user();
        $teamId = $user->currentTeam->getKey();

        /** @var Company|null $company */
        $company = $this->route('company');

        return array_merge([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'partita_iva' => ['sometimes', 'nullable', 'string', 'max:64', Rule::unique('companies', 'partita_iva')->where('team_id', $teamId)->ignore($company?->getKey())],
        ], new ValidCustomFields($teamId, 'company', isUpdate: true)->toRules($this->input('custom_fields')));
    }
}
