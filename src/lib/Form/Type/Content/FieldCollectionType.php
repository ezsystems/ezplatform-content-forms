<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformContentForms\Form\Type\Content;

use EzSystems\EzPlatformContentForms\Event\ContentCreateFieldOptionsEvent;
use EzSystems\EzPlatformContentForms\Event\ContentFormEvents;
use EzSystems\EzPlatformContentForms\Event\ContentUpdateFieldOptionsEvent;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class FieldCollectionType extends CollectionType
{
    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ) {
        parent::buildForm($builder, $options);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $data = $event->getData();

            foreach ($form as $name => $child) {
                $form->remove($name);
            }

            // Then add all rows again in the correct order
            foreach ($data as $name => $value) {
                $entryOptions = array_replace([
                    'property_path' => '[' . $name . ']',
                ], $options['entry_options']);
                $entryData = $data[$name];

                if ($this->isContentUpdate($entryOptions)) {
                    /** @var \EzSystems\EzPlatformContentForms\Event\ContentUpdateFieldOptionsEvent $contentUpdateFieldOptionsEvent */
                    $contentUpdateFieldOptionsEvent = $this->eventDispatcher->dispatch(
                        new ContentUpdateFieldOptionsEvent(
                            $entryOptions['content'],
                            $entryOptions['contentUpdateStruct'],
                            $form,
                            $entryData,
                            $entryOptions
                        ),
                        ContentFormEvents::CONTENT_EDIT_FIELD_OPTIONS
                    );

                    $entryOptions = $contentUpdateFieldOptionsEvent->getOptions();
                } elseif ($this->isContentCreate($entryOptions)) {
                    /** @var \EzSystems\EzPlatformContentForms\Event\ContentCreateFieldOptionsEvent $contentUpdateFieldOptionsEvent */
                    $contentCreateFieldOptionsEvent = $this->eventDispatcher->dispatch(
                        new ContentCreateFieldOptionsEvent(
                            $entryOptions['contentCreateStruct'],
                            $form,
                            $entryData,
                            $entryOptions
                        ),
                        ContentFormEvents::CONTENT_CREATE_FIELD_OPTIONS
                    );

                    $entryOptions = $contentCreateFieldOptionsEvent->getOptions();
                }

                $form->add($name, $options['entry_type'], $entryOptions);
            }
        });
    }

    private function isContentCreate(array $entryOptions): bool
    {
        return !empty($entryOptions['contentCreateStruct']);
    }

    private function isContentUpdate(array $entryOptions): bool
    {
        return !empty($entryOptions['content']) && !empty($entryOptions['contentUpdateStruct']);
    }
}
