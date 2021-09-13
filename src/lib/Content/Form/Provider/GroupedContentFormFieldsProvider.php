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

    public function getGroupedFields(array $fieldsDataForm): array
    {
        $fieldsGroups = $this->fieldsGroupsList->getGroups();
        $groupedFields = [];

        foreach ($fieldsDataForm as $fieldForm) {
            /** @var \EzSystems\EzPlatformContentForms\Data\Content\FieldData $fieldData */
            $fieldData = $fieldForm->getViewData();
            $fieldGroupIdentifier = $this->fieldsGroupsList->getFieldGroup($fieldData->fieldDefinition);
            $fieldGroupName = $fieldsGroups[$fieldGroupIdentifier] ?? $this->fieldsGroupsList->getDefaultGroup();

            $groupedFields[$fieldGroupName][] = $fieldForm->getName();
        }

        return $groupedFields;
    }
}
