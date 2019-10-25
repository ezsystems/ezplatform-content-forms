<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformContentForms\Data\Content;

trait ContentData
{
    /**
     * @var \EzSystems\EzPlatformContentForms\Data\Content\FieldData[]
     */
    protected $fieldsData;

    public function addFieldData(FieldData $fieldData)
    {
        $this->fieldsData[$fieldData->fieldDefinition->identifier] = $fieldData;
    }
}
