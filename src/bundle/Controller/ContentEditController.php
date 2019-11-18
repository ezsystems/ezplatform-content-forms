<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformContentFormsBundle\Controller;

use eZ\Bundle\EzPublishCoreBundle\Controller;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use EzSystems\EzPlatformContentForms\Content\View\ContentCreateDraftView;
use EzSystems\EzPlatformContentForms\Content\View\ContentCreateSuccessView;
use EzSystems\EzPlatformContentForms\Content\View\ContentCreateView;
use EzSystems\EzPlatformContentForms\Content\View\ContentEditSuccessView;
use EzSystems\EzPlatformContentForms\Content\View\ContentEditView;
use EzSystems\EzPlatformContentForms\Data\Content\CreateContentDraftData;
use EzSystems\EzPlatformContentForms\Form\ActionDispatcher\ActionDispatcherInterface;
use EzSystems\EzPlatformContentForms\Form\Type\Content\ContentDraftCreateType;
use Symfony\Component\HttpFoundation\Request;

class ContentEditController extends Controller
{
    /** @var ContentTypeService */
    private $contentTypeService;

    /** @var ContentService */
    private $contentService;

    /** @var ActionDispatcherInterface */
    private $contentActionDispatcher;

    public function __construct(
        ContentTypeService $contentTypeService,
        ContentService $contentService,
        ActionDispatcherInterface $contentActionDispatcher
    ) {
        $this->contentTypeService = $contentTypeService;
        $this->contentService = $contentService;
        $this->contentActionDispatcher = $contentActionDispatcher;
    }

    /**
     * Displays and processes a content creation form. Showing the form does not create a draft in the repository.
     *
     * @param \EzSystems\EzPlatformContentForms\Content\View\ContentCreateView $view
     *
     * @return \EzSystems\EzPlatformContentForms\Content\View\ContentCreateView
     */
    public function createWithoutDraftAction(ContentCreateView $view): ContentCreateView
    {
        return $view;
    }

    /**
     * @param \EzSystems\EzPlatformContentForms\Content\View\ContentCreateSuccessView $view
     *
     * @return \EzSystems\EzPlatformContentForms\Content\View\ContentCreateSuccessView
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function createWithoutDraftSuccessAction(ContentCreateSuccessView $view): ContentCreateSuccessView
    {
        return $view;
    }

    /**
     * Displays a draft creation form that creates a content draft from an existing content.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int|null $contentId
     * @param int $fromVersionNo
     * @param string $fromLanguage
     *
     * @return \EzSystems\EzPlatformContentForms\Content\View\ContentCreateDraftView|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    public function createContentDraftAction(
        Request $request,
        ?int $contentId = null,
        ?int $fromVersionNo = null,
        ?string $fromLanguage = null
    ) {
        $createContentDraft = new CreateContentDraftData();
        $contentInfo = null;
        $contentType = null;

        if ($contentId !== null) {
            $createContentDraft->contentId = $contentId;

            $contentInfo = $this->contentService->loadContentInfo($contentId);
            $contentType = $this->contentTypeService->loadContentType($contentInfo->contentTypeId);
            $createContentDraft->fromVersionNo = $fromVersionNo ?: $contentInfo->currentVersionNo;
            $createContentDraft->fromLanguage = $fromLanguage ?: $contentInfo->mainLanguageCode;
        }

        $form = $this->createForm(
            ContentDraftCreateType::class,
            $createContentDraft,
            [
                'action' => $this->generateUrl('ezplatform.content.draft.create'),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && null !== $form->getClickedButton()) {
            $this->contentActionDispatcher->dispatchFormAction($form, $createContentDraft, $form->getClickedButton()->getName());
            if ($response = $this->contentActionDispatcher->getResponse()) {
                return $response;
            }
        }

        return new ContentCreateDraftView(null, [
            'form' => $form->createView(),
            'contentInfo' => $contentInfo,
            'contentType' => $contentType,
        ]);
    }

    /**
     * @param \EzSystems\EzPlatformContentForms\Content\View\ContentEditView $view
     *
     * @return \EzSystems\EzPlatformContentForms\Content\View\ContentEditView
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function editVersionDraftAction(ContentEditView $view): ContentEditView
    {
        return $view;
    }

    /**
     * @param \EzSystems\EzPlatformContentForms\Content\View\ContentEditSuccessView $view
     *
     * @return \EzSystems\EzPlatformContentForms\Content\View\ContentEditSuccessView
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function editVersionDraftSuccessAction(ContentEditSuccessView $view): ContentEditSuccessView
    {
        return $view;
    }
}
