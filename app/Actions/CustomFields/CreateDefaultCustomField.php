<?php

declare(strict_types=1);

namespace App\Actions\CustomFields;

use App\Enums\CustomFields\CompanyField as CompanyCustomField;
use App\Enums\CustomFields\NoteField as NoteCustomField;
use App\Enums\CustomFields\OpportunityField as OpportunityCustomField;
use App\Enums\CustomFields\PeopleField as PeopleCustomField;
use App\Enums\CustomFields\TaskField as TaskCustomField;
use Relaticle\CustomFields\Contracts\CustomsFieldsMigrators;
use Relaticle\CustomFields\Data\CustomFieldData;
use Relaticle\CustomFields\Data\CustomFieldSectionData;
use Relaticle\CustomFields\Data\CustomFieldSettingsData;
use Relaticle\CustomFields\Enums\CustomFieldSectionType;
use Relaticle\CustomFields\Models\CustomField;

/**
 * Creates a single default custom field for a tenant, driven by the entity's
 * field enum (the single source of truth for default field definitions).
 */
final readonly class CreateDefaultCustomField
{
    public function __construct(private CustomsFieldsMigrators $migrator) {}

    /**
     * @param  class-string  $model
     */
    public function execute(
        string $tenantId,
        string $model,
        CompanyCustomField|OpportunityCustomField|PeopleCustomField|TaskCustomField|NoteCustomField $enum,
    ): CustomField {
        $this->migrator->setTenantId($tenantId);

        $fieldData = new CustomFieldData(
            name: $enum->getDisplayName(),
            code: $enum->value,
            type: $enum->getFieldType(),
            section: new CustomFieldSectionData(
                name: 'General',
                code: 'general',
                type: CustomFieldSectionType::HEADLESS,
            ),
            systemDefined: $enum->isSystemDefined(),
            width: $enum->getWidth(),
            settings: new CustomFieldSettingsData(
                list_toggleable_hidden: $enum->isListToggleableHidden(),
                enable_option_colors: $enum->hasColorOptions(),
                allow_multiple: $enum->allowsMultipleValues(),
                max_values: $enum->getMaxValues(),
                unique_per_entity_type: $enum->isUniquePerEntityType(),
            ),
        );

        $migrator = $this->migrator->new(model: $model, fieldData: $fieldData);

        $options = $enum->getOptions();
        if ($options !== null) {
            $migrator->options($options);
        }

        return $migrator->create();
    }
}
