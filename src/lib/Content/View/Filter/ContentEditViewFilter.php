<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformContentForms\Content\View\Filter;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewBuilderParametersEvent;
use eZ\Publish\Core\MVC\Symfony\View\ViewEvents;
use EzSystems\EzPlatformContentForms\Data\Content\ContentUpdateData;
use EzSystems\EzPlatformContentForms\Data\Mapper\ContentUpdateMapper;
use EzSystems\EzPlatformContentForms\Form\Type\Content\ContentEditType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class ContentEditViewFilter implements EventSubscriberInterface
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \Symfony\Component\Form\FormFactoryInterface */
    private $formFactory;

    /** @var \eZ\Publish\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface */
    private $languagePreferenceProvider;

    /**
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \eZ\Publish\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface $languagePreferenceProvider
     */
    public function __construct(
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        FormFactoryInterface $formFactory,
        UserLanguagePreferenceProviderInterface $languagePreferenceProvider
    ) {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->formFactory = $formFactory;
        $this->languagePreferenceProvider = $languagePreferenceProvider;
    }

    public static function getSubscribedEvents()
    {
        return [ViewEvents::FILTER_BUILDER_PARAMETERS => 'handleContentEditForm'];
    }

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewBuilderParametersEvent $event
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function handleContentEditForm(FilterViewBuilderParametersEvent $event)
    {
        if ('ez_content_edit:editVersionDraftAction' !== $event->getParameters()->get('_controller')) {
            return;
        }

        $request = $event->getRequest();
        $languageCode = $request->attributes->get('language');
        $contentId = $request->attributes->getInt('contentId');
        $contentDraft = $this->contentService->loadContent(
            $contentId,
            [$languageCode], // @todo: rename to languageCode in 3.0
            $request->attributes->getInt('versionNo')
        );
        $currentContent = $this->contentService->loadContent($contentId);
        $currentFields = $currentContent->getFields();

        $contentType = $this->contentTypeService->loadContentType(
            $contentDraft->contentInfo->contentTypeId,
            $this->languagePreferenceProvider->getPreferredLanguages()
        );

        $contentUpdate = $this->resolveContentEditData(
            $contentDraft,
            $languageCode,
            $contentType,
            $currentFields,
        );
        $form = $this->resolveContentEditForm(
            $contentUpdate,
            $languageCode,
            $contentDraft
        );

        $event->getParameters()->add([
            'form' => $form->handleRequest($request),
            'validate' => (bool)$request->get('validate', false),
        ]);
    }

    /**
     * @param array<\eZ\Publish\API\Repository\Values\Content\Field> $currentFields
     */
    private function resolveContentEditData(
        Content $content,
        string $languageCode,
        ContentType $contentType,
        array $currentFields
    ): ContentUpdateData {
        $contentUpdateMapper = new ContentUpdateMapper();

        return $contentUpdateMapper->mapToFormData($content, [
            'languageCode' => $languageCode,
            'contentType' => $contentType,
            'currentFields' => $currentFields,
        ]);
    }

    /**
     * @param \EzSystems\EzPlatformContentForms\Data\Content\ContentUpdateData $contentUpdate
     * @param string $languageCode
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    private function resolveContentEditForm(
        ContentUpdateData $contentUpdate,
        string $languageCode,
        Content $content
    ): FormInterface {
        return $this->formFactory->create(
            ContentEditType::class,
            $contentUpdate,
            [
                'languageCode' => $languageCode,
                'mainLanguageCode' => $content->contentInfo->mainLanguageCode,
                'content' => $content,
                'contentUpdateStruct' => $contentUpdate,
                'drafts_enabled' => true,
            ]
        );
    }
}
