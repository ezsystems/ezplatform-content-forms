<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformContentForms\Form\Processor\User;

use eZ\Publish\API\Repository\UserService;
use EzSystems\EzPlatformContentForms\Data\User\UserCreateData;
use EzSystems\EzPlatformContentForms\Event\ContentFormEvents;
use EzSystems\EzPlatformContentForms\Event\FormActionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Listens for and processes User create events.
 */
class UserCreateFormProcessor implements EventSubscriberInterface
{
    /** @var UserService */
    private $userService;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /**
     * @param UserService $userService
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        UserService $userService,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->userService = $userService;
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents()
    {
        return [
            ContentFormEvents::USER_CREATE => ['processCreate', 20],
        ];
    }

    public function processCreate(FormActionEvent $event)
    {
        $data = $data = $event->getData();

        if (!$data instanceof UserCreateData) {
            return;
        }

        $form = $event->getForm();
        $languageCode = $form->getConfig()->getOption('languageCode');

        $this->setContentFields($data, $languageCode);
        $user = $this->userService->createUser($data, $data->getParentGroups());

        $redirectUrl = $form['redirectUrlAfterPublish']->getData() ?: $this->urlGenerator->generate(
            '_ez_content_view', [
                'contentId' => $user->id,
                'locationId' => $user->contentInfo->mainLocationId,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $event->setResponse(new RedirectResponse($redirectUrl));
    }

    /**
     * @param UserCreateData $data
     * @param string $languageCode
     */
    private function setContentFields(UserCreateData $data, string $languageCode): void
    {
        foreach ($data->fieldsData as $fieldDefIdentifier => $fieldData) {
            $data->setField($fieldDefIdentifier, $fieldData->value, $languageCode);
        }
    }
}
