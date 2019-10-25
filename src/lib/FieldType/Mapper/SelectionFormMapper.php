<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformContentForms\FieldType\Mapper;

use EzSystems\EzPlatformContentForms\Data\Content\FieldData;
use EzSystems\EzPlatformContentForms\FieldType\FieldValueFormMapperInterface;
use EzSystems\EzPlatformContentForms\Form\Type\FieldType\SelectionFieldType;
use Symfony\Component\Form\FormInterface;

class SelectionFormMapper implements FieldValueFormMapperInterface
{
    /**
    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data)
    {
        $fieldDefinition = $data->fieldDefinition;
        $formConfig = $fieldForm->getConfig();
        $languageCode = $fieldForm->getConfig()->getOption('languageCode');

        $choices = $fieldDefinition->fieldSettings['options'];

        if (!empty($fieldDefinition->fieldSettings['multilingualOptions'][$languageCode])) {
            $choices = $fieldDefinition->fieldSettings['multilingualOptions'][$languageCode];
        } elseif (!empty($fieldDefinition->fieldSettings['multilingualOptions'][$fieldDefinition->mainLanguageCode])) {
            $choices = $fieldDefinition->fieldSettings['multilingualOptions'][$fieldDefinition->mainLanguageCode];
        }

        $fieldForm
            ->add(
                $formConfig->getFormFactory()->createBuilder()
                    ->create(
                        'value',
                        SelectionFieldType::class,
                        [
                            'required' => $fieldDefinition->isRequired,
                            'label' => $fieldDefinition->getName(),
                            'multiple' => $fieldDefinition->fieldSettings['isMultiple'],
                            'choices' => array_flip($choices),
                        ]
                    )
                    ->setAutoInitialize(false)
                    ->getForm()
            );
    }
     */
    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data)
    {
        $fieldDefinition = $data->fieldDefinition;
        $formConfig = $fieldForm->getConfig();
        $languageCode = $fieldForm->getConfig()->getOption('languageCode');

        $choices = $fieldDefinition->fieldSettings['options'];

        if (!empty($fieldDefinition->fieldSettings['multilingualOptions'][$languageCode])) {
            $choices = $fieldDefinition->fieldSettings['multilingualOptions'][$languageCode];
        } elseif (!empty($fieldDefinition->fieldSettings['multilingualOptions'][$fieldDefinition->mainLanguageCode])) {
            $choices = $fieldDefinition->fieldSettings['multilingualOptions'][$fieldDefinition->mainLanguageCode];
        }

        $fieldForm
            ->add(
                $formConfig->getFormFactory()->createBuilder()
                           ->create(
                               'value',
                               SelectionFieldType::class,
                               [
                                   'required' => $fieldDefinition->isRequired,
                                   'label' => $fieldDefinition->getName(),
                                   'multiple' => $fieldDefinition->fieldSettings['isMultiple'],
                                   'choices' => array_flip($choices),
                               ]
                           )
                           ->setAutoInitialize(false)
                           ->getForm()
            );
    }
}
