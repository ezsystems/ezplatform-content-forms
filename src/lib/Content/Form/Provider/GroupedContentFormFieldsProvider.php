<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\ContentForms\Content\Form\Provider;

use eZ\Publish\Core\Helper\FieldsGroups\FieldsGroupsList;
use Ibexa\Contracts\ContentForms\Content\Form\Provider\GroupedContentFormFieldsProviderInterface;

final class GroupedContentFormFieldsProvider implements GroupedContentFormFieldsProviderInterface
{
    /** @var \eZ\Publish\Core\Helper\FieldsGroups\FieldsGroupsList */
    private $fieldsGroupsList;

    public function __construct(FieldsGroupsList $fieldsGroupsList)
    {
        $this->fieldsGroupsList = $fieldsGroupsList;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupedFields(array $fieldsDataForm): array
    {
        $groupedFields = [];

        foreach ($fieldsDataForm as $fieldForm) {
            /** @var \EzSystems\EzPlatformContentForms\Data\Content\FieldData $fieldData */
            $fieldData = $fieldForm->getViewData();

            $fieldGroup = $this->fieldsGroupsList->getFieldGroup(
                $fieldData->fieldDefinition
            );

            $groupedFields[$fieldGroup][] = $fieldForm->getName();
        }

        return $this->sortGroupedFields($groupedFields);
    }

    /**
     * Makes sure fields groups order in the same like in YAML definition.
     */
    private function sortGroupedFields(array $groupedFields): array
    {
        $groupedFieldsList = [];

        $fieldsGroups = $this->fieldsGroupsList->getGroups();
        foreach ($fieldsGroups as $fieldGroupIdentifier => $fieldGroupName) {
            if (array_key_exists($fieldGroupIdentifier, $groupedFields)) {
                $groupedFieldsList[$fieldGroupName] = $groupedFields[$fieldGroupIdentifier];
            }
        }

        return $groupedFieldsList;
    }
}
