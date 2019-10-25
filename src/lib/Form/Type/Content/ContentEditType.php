<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformContentForms\Form\Type\Content;

use eZ\Publish\API\Repository\Values\Content\ContentStruct;
use EzSystems\EzPlatformContentForms\Form\EventSubscriber\SuppressValidationSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for content edition (create/update).
 * Underlying data will be either \EzSystems\EzPlatformContentForms\Data\Content\ContentCreateData or \EzSystems\EzPlatformContentForms\Data\Content\ContentUpdateData
 * depending on the context (create or update).
 */
class ContentEditType extends AbstractType
{
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix()
    {
        return 'ezplatform_content_forms_content_edit';
    }

    public function getParent()
    {
        return BaseContentType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('publish', SubmitType::class, ['label' => 'Publish']);

        if ($options['drafts_enabled']) {
            $builder
                ->add('saveDraft', SubmitType::class, ['label' => 'Save draft'])
                ->add('cancel', SubmitType::class, [
                    'label' => 'Cancel',
                    'attr' => ['formnovalidate' => 'formnovalidate'],
                ]);
            $builder->addEventSubscriber(new SuppressValidationSubscriber());
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'drafts_enabled' => false,
                'data_class' => ContentStruct::class,
                'translation_domain' => 'ezplatform_content_forms_content',
                'intent' => 'update',
            ]);
    }
}
