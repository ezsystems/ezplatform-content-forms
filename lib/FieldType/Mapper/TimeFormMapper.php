<?php

/**
 * This file is part of the eZ RepositoryForms package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformContentForms\FieldType\Mapper;

use EzSystems\EzPlatformContentForms\Data\Content\FieldData;
use EzSystems\EzPlatformContentForms\FieldType\FieldDefinitionFormMapperInterface;
use EzSystems\EzPlatformContentForms\FieldType\FieldValueFormMapperInterface;
use EzSystems\EzPlatformContentForms\Form\Type\FieldType\TimeFieldType;
use Symfony\Component\Form\FormInterface;

/**
 * FormMapper for eztime FieldType.
 */
class TimeFormMapper implements FieldValueFormMapperInterface
{
    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data)
    {
        $fieldDefinition = $data->fieldDefinition;
        $fieldSettings = $fieldDefinition->getFieldSettings();
        $formConfig = $fieldForm->getConfig();

        $fieldForm
            ->add(
                $formConfig->getFormFactory()->createBuilder()
                    ->create('value', TimeFieldType::class, [
                        'with_seconds' => $fieldSettings['useSeconds'],
                        'required' => $fieldDefinition->isRequired,
                        'label' => $fieldDefinition->getName(),
                    ])
                    ->setAutoInitialize(false)
                    ->getForm()
            );
    }
}
