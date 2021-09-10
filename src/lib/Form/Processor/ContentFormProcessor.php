<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformContentForms\Form\Processor;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentStruct;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use EzSystems\EzPlatformContentForms\Data\Content\ContentCreateData;
use EzSystems\EzPlatformContentForms\Data\Content\ContentUpdateData;
use EzSystems\EzPlatformContentForms\Data\NewnessCheckable;
use EzSystems\EzPlatformContentForms\Event\ContentFormEvents;
use EzSystems\EzPlatformContentForms\Event\FormActionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Listens for and processes RepositoryForm events: publish, remove draft, save draft...
 */
class ContentFormProcessor implements EventSubscriberInterface
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \eZ\Publish\API\Repository\LocationService */
    private $locationService;

    /** @var \Symfony\Component\Routing\RouterInterface */
    private $router;

    /**
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \eZ\Publish\API\Repository\URLAliasService $urlAliasService
     */
    public function __construct(
        ContentService $contentService,
        LocationService $locationService,
        RouterInterface $router
    ) {
        $this->contentService = $contentService;
        $this->locationService = $locationService;
        $this->router = $router;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ContentFormEvents::CONTENT_PUBLISH => ['processPublish', 10],
            ContentFormEvents::CONTENT_CANCEL => ['processCancel', 10],
            ContentFormEvents::CONTENT_SAVE_DRAFT => ['processSaveDraft', 10],
            ContentFormEvents::CONTENT_CREATE_DRAFT => ['processCreateDraft', 10],
        ];
    }

    /**
     * @param \EzSystems\EzPlatformContentForms\Event\FormActionEvent $event
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    public function processSaveDraft(FormActionEvent $event)
    {
        /** @var \EzSystems\EzPlatformContentForms\Data\Content\ContentCreateData|\EzSystems\EzPlatformContentForms\Data\Content\ContentUpdateData $data */
        $data = $event->getData();
        $form = $event->getForm();

        $formConfig = $form->getConfig();
        $languageCode = $formConfig->getOption('languageCode');
        $draft = $this->saveDraft($data, $languageCode, []);
        $referrerLocation = $event->getOption('referrerLocation');
        $contentLocation = $this->resolveLocation($draft, $referrerLocation, $data);

        $event->setPayload('content', $draft);
        $event->setPayload('is_new', $draft->contentInfo->isDraft());

        $defaultUrl = $this->router->generate('ezplatform.content.draft.edit', [
            'contentId' => $draft->id,
            'versionNo' => $draft->getVersionInfo()->versionNo,
            'language' => $languageCode,
            'locationId' => null !== $contentLocation ? $contentLocation->id : null,
        ]);
        $event->setResponse(new RedirectResponse($formConfig->getAction() ?: $defaultUrl));
    }

    /**
     * @param \EzSystems\EzPlatformContentForms\Event\FormActionEvent $event
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    public function processPublish(FormActionEvent $event)
    {
        /** @var \EzSystems\EzPlatformContentForms\Data\Content\ContentCreateData|\EzSystems\EzPlatformContentForms\Data\Content\ContentUpdateData $data */
        $data = $event->getData();
        $form = $event->getForm();

        $draft = $this->saveDraft($data, $form->getConfig()->getOption('languageCode'));
        $versionInfo = $draft->versionInfo;
        $content = $this->contentService->publishVersion($versionInfo, [$versionInfo->initialLanguageCode]);

        $event->setPayload('content', $content);
        $event->setPayload('is_new', $draft->contentInfo->isDraft());

        $redirectUrl = $form['redirectUrlAfterPublish']->getData() ?: $this->router->generate(
            '_ez_content_view', [
                'contentId' => $content->id,
                'locationId' => $content->contentInfo->mainLocationId,
            ]
        );

        $event->setResponse(new RedirectResponse($redirectUrl));
    }

    /**
     * @param \EzSystems\EzPlatformContentForms\Event\FormActionEvent $event
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function processCancel(FormActionEvent $event)
    {
        /** @var \EzSystems\EzPlatformContentForms\Data\Content\ContentCreateData|\EzSystems\EzPlatformContentForms\Data\Content\ContentUpdateData $data */
        $data = $event->getData();

        if ($data->isNew()) {
            $parentLocation = $this->locationService->loadLocation($data->getLocationStructs()[0]->parentLocationId);
            $response = new RedirectResponse($this->router->generate(
                '_ez_content_view', [
                    'contentId' => $parentLocation->contentId,
                    'locationId' => $parentLocation->id,
                ]
            ));
            $event->setResponse($response);

            return;
        }

        $content = $data->contentDraft;
        $contentInfo = $content->contentInfo;
        $versionInfo = $data->contentDraft->getVersionInfo();

        $event->setPayload('content', $content);

        // if there is only one version you have to remove whole content instead of a version itself
        if (1 === count($this->contentService->loadVersions($contentInfo))) {
            $parentLocation = $this->locationService->loadParentLocationsForDraftContent($versionInfo)[0];
            $redirectionLocationId = $parentLocation->id;
            $redirectionContentId = $parentLocation->contentId;
        } else {
            $redirectionLocationId = $contentInfo->mainLocationId;
            $redirectionContentId = $contentInfo->id;
        }

        $this->contentService->deleteVersion($versionInfo);

        $url = $this->router->generate(
            '_ez_content_view', [
                'contentId' => $redirectionContentId,
                'locationId' => $redirectionLocationId,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $event->setResponse(new RedirectResponse($url));
    }

    /**
     * @param \EzSystems\EzPlatformContentForms\Event\FormActionEvent $event
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function processCreateDraft(FormActionEvent $event)
    {
        /** @var $createContentDraft \EzSystems\EzPlatformContentForms\Data\Content\CreateContentDraftData */
        $createContentDraft = $event->getData();

        $contentInfo = $this->contentService->loadContentInfo($createContentDraft->contentId);
        $versionInfo = $this->contentService->loadVersionInfo($contentInfo, $createContentDraft->fromVersionNo);
        $contentDraft = $this->contentService->createContentDraft($contentInfo, $versionInfo);
        $referrerLocation = $event->getOption('referrerLocation');

        $event->setPayload('content', $contentDraft);
        $event->setPayload('is_new', $contentDraft->contentInfo->isDraft());

        $contentEditUrl = $this->router->generate('ezplatform.content.draft.edit', [
            'contentId' => $contentDraft->id,
            'versionNo' => $contentDraft->getVersionInfo()->versionNo,
            'language' => $contentDraft->contentInfo->mainLanguageCode,
            'locationId' => null !== $referrerLocation ? $referrerLocation->id : null,
        ]);
        $event->setResponse(new RedirectResponse($contentEditUrl));
    }

    /**
     * Saves content draft corresponding to $data.
     * Depending on the nature of $data (create or update data), the draft will either be created or simply updated.
     *
     * @param ContentStruct|\EzSystems\EzPlatformContentForms\Data\Content\ContentCreateData|\EzSystems\EzPlatformContentForms\Data\Content\ContentUpdateData $data
     * @param $languageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    private function saveDraft(ContentStruct $data, string $languageCode, ?array $fieldIdentifiersToValidate = null)
    {
        $mainLanguageCode = $this->resolveMainLanguageCode($data);
        foreach ($data->fieldsData as $fieldDefIdentifier => $fieldData) {
            if ($mainLanguageCode != $languageCode && !$fieldData->fieldDefinition->isTranslatable) {
                continue;
            }

            $data->setField($fieldDefIdentifier, $fieldData->value, $languageCode);
        }

        if ($data->isNew()) {
            $contentDraft = $this->contentService->createContent($data, $data->getLocationStructs(), $fieldIdentifiersToValidate);
        } else {
            $contentDraft = $this->contentService->updateContent($data->contentDraft->getVersionInfo(), $data, $fieldIdentifiersToValidate);
        }

        return $contentDraft;
    }

    /**
     * @param \EzSystems\EzPlatformContentForms\Data\Content\ContentCreateData|\EzSystems\EzPlatformContentForms\Data\Content\ContentUpdateData $data
     *
     * @return string
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    private function resolveMainLanguageCode($data): string
    {
        if (!$data instanceof ContentUpdateData && !$data instanceof ContentCreateData) {
            throw new InvalidArgumentException(
                '$data',
                'Expected ContentUpdateData or ContentCreateData'
            );
        }

        return $data->isNew()
            ? $data->mainLanguageCode
            : $data->contentDraft->getVersionInfo()->getContentInfo()->mainLanguageCode;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param \eZ\Publish\API\Repository\Values\Content\Location|null $referrerLocation
     * @param \EzSystems\EzPlatformContentForms\Data\NewnessCheckable $data
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location|null
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function resolveLocation(Content $content, ?Location $referrerLocation, NewnessCheckable $data): ?Location
    {
        if ($data->isNew() || (!$content->contentInfo->published && null === $content->contentInfo->mainLocationId)) {
            return null; // no location exists until new content is published
        }

        return $referrerLocation ?? $this->locationService->loadLocation($content->contentInfo->mainLocationId);
    }
}
