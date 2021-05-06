<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformContentForms\Tests\FieldType\Mapper;

use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use EzSystems\EzPlatformContentForms\FieldType\Mapper\FormTypeBasedFieldValueFormMapper;

class FormTypeBasedFieldValueFormMapperTest extends BaseMapperTest
{
    public function testMapFieldValueFormNoLanguageCode()
    {
        $mapper = new FormTypeBasedFieldValueFormMapper($this->fieldTypeService);

        $fieldDefinition = new FieldDefinition([
            'names' => [],
            'isRequired' => false,
            'fieldTypeIdentifier' => 'ezselection',
            'fieldSettings' => ['isMultiple' => false, 'options' => []],
        ]);

        $this->data->expects($this->once())
            ->method('__get')
            ->with('fieldDefinition')
            ->willReturn($fieldDefinition);

        $this->config
            ->method('getOption')
            ->willReturnMap([
                ['languageCode', null, 'eng-GB'],
                ['mainLanguageCode', null, 'eng-GB'],
            ]);

        $mapper->mapFieldValueForm($this->fieldForm, $this->data);
    }

    public function testMapFieldValueFormWithLanguageCode()
    {
        $mapper = new FormTypeBasedFieldValueFormMapper($this->fieldTypeService);

        $fieldDefinition = new FieldDefinition([
            'names' => ['eng-GB' => 'foo'],
            'isRequired' => false,
            'fieldTypeIdentifier' => 'ezselection',
            'fieldSettings' => ['isMultiple' => false, 'options' => []],
        ]);
        $this->data->expects($this->once())
            ->method('__get')
            ->with('fieldDefinition')
            ->willReturn($fieldDefinition);

        $this->config
            ->method('getOption')
            ->with('languageCode')
            ->willReturn('eng-GB');

        $mapper->mapFieldValueForm($this->fieldForm, $this->data);
    }
}
