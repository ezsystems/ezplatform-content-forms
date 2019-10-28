<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformContentForms\FieldType\Mapper;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\FieldType\User\Value as ApiUserValue;
use EzSystems\EzPlatformContentForms\Data\Content\FieldData;
use EzSystems\EzPlatformContentForms\Data\ContentTranslationData;
use EzSystems\EzPlatformContentForms\Data\User\UserAccountFieldData;
use EzSystems\EzPlatformContentForms\FieldType\FieldDefinitionFormMapperInterface;
use EzSystems\EzPlatformContentForms\FieldType\FieldValueFormMapperInterface;
use EzSystems\EzPlatformContentForms\Form\Type\FieldType\UserAccountFieldType;
use EzSystems\EzPlatformContentForms\Validator\Constraints\UserAccountPassword;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\AlreadySubmittedException;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Maps a user FieldType.
 */
final class UserAccountFieldValueFormMapper implements FieldValueFormMapperInterface
{
    /**
     * Maps Field form to current FieldType based on the configured form type (self::$formType).
     *
     * @param FormInterface $fieldForm Form for the current Field.
     * @param FieldData $data Underlying data for current Field form.
     *
     * @throws AlreadySubmittedException
     * @throws LogicException
     * @throws UnexpectedTypeException
     * @throws InvalidOptionsException
     */
    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data)
    {
        $fieldDefinition = $data->fieldDefinition;
        $formConfig = $fieldForm->getConfig();
        $rootForm = $fieldForm->getRoot()->getRoot();
        $formIntent = $rootForm->getConfig()->getOption('intent');
        $isTranslation = $rootForm->getData() instanceof ContentTranslationData;
        $formBuilder = $formConfig->getFormFactory()->createBuilder()
            ->create('value', UserAccountFieldType::class, [
                'required' => true,
                'label' => $fieldDefinition->getName(),
                'intent' => $formIntent,
                'constraints' => [
                    new UserAccountPassword(['contentType' => $rootForm->getData()->contentType]),
                ],
            ]);

        if ($isTranslation) {
            $formBuilder->addModelTransformer($this->getModelTransformerForTranslation($fieldDefinition));
        } else {
            $formBuilder->addModelTransformer($this->getModelTransformer());
        }

        $formBuilder->setAutoInitialize(false);

        $fieldForm->add($formBuilder->getForm());
    }

    /**
     * Fake method to set the translation domain for the extractor.
     *
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'translation_domain' => 'ezplatform_content_forms_content',
            ]);
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     *
     * @return \Symfony\Component\Form\CallbackTransformer
     */
    public function getModelTransformerForTranslation(FieldDefinition $fieldDefinition): CallbackTransformer
    {
        return new CallbackTransformer(
            function (ApiUserValue $data) {
                return new UserAccountFieldData($data->login, null, $data->email, $data->enabled);
            },
            function (UserAccountFieldData $submittedData) use ($fieldDefinition) {
                $userValue = clone $fieldDefinition->defaultValue;
                $userValue->login = $submittedData->username;
                $userValue->email = $submittedData->email;
                $userValue->enabled = $submittedData->enabled;

                return $userValue;
            }
        );
    }

    /**
     * @return \Symfony\Component\Form\CallbackTransformer
     */
    public function getModelTransformer(): CallbackTransformer
    {
        return new CallbackTransformer(
            function (ApiUserValue $data) {
                return new UserAccountFieldData($data->login, null, $data->email, $data->enabled);
            },
            function (UserAccountFieldData $submittedData) {
                return $submittedData;
            }
        );
    }
}
