<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformContentForms\Content\View\Filter;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewBuilderParametersEvent;
use eZ\Publish\Core\MVC\Symfony\View\ViewEvents;
use EzSystems\EzPlatformContentForms\Data\Content\ContentCreateData;
use EzSystems\EzPlatformContentForms\Data\Mapper\ContentCreateMapper;
use EzSystems\EzPlatformContentForms\Form\Type\Content\ContentEditType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class ContentCreateViewFilter implements EventSubscriberInterface
{
    /** @var \eZ\Publish\API\Repository\LocationService */
    private $locationService;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \Symfony\Component\Form\FormFactoryInterface */
    private $formFactory;

    /** @var \eZ\Publish\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface */
    private $languagePreferenceProvider;

    /**
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \eZ\Publish\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface $languagePreferenceProvider
     */
    public function __construct(
        LocationService $locationService,
        ContentTypeService $contentTypeService,
        FormFactoryInterface $formFactory,
        UserLanguagePreferenceProviderInterface $languagePreferenceProvider
    ) {
        $this->locationService = $locationService;
        $this->contentTypeService = $contentTypeService;
        $this->formFactory = $formFactory;
        $this->languagePreferenceProvider = $languagePreferenceProvider;
    }

    public static function getSubscribedEvents()
    {
        return [ViewEvents::FILTER_BUILDER_PARAMETERS => 'handleContentCreateForm'];
    }

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewBuilderParametersEvent $event
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function handleContentCreateForm(FilterViewBuilderParametersEvent $event)
    {
        if ('ez_content_edit:createWithoutDraftAction' !== $event->getParameters()->get('_controller')) {
            return;
        }

        $request = $event->getRequest();
        $languageCode = $request->attributes->get('language');

        $contentType = $this->contentTypeService->loadContentTypeByIdentifier(
            $request->attributes->get('contentTypeIdentifier'),
            $this->languagePreferenceProvider->getPreferredLanguages()
        );
        $location = $this->locationService->loadLocation(
            $request->attributes->getInt('parentLocationId')
        );

        $contentCreateData = $this->resolveContentCreateData($contentType, $location, $languageCode);
        $form = $this->resolveContentCreateForm(
            $contentCreateData,
            $languageCode,
            false
        );

        $event->getParameters()->add(['form' => $form->handleRequest($request)]);
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $languageCode
     *
     * @return \EzSystems\EzPlatformContentForms\Data\Content\ContentCreateData
     */
    private function resolveContentCreateData(
        ContentType $contentType,
        Location $location,
        string $languageCode
    ): ContentCreateData {
        $contentCreateMapper = new ContentCreateMapper();

        return $contentCreateMapper->mapToFormData(
            $contentType,
            [
                'mainLanguageCode' => $languageCode,
                'parentLocation' => $this->locationService->newLocationCreateStruct($location->id, $contentType),
            ]
        );
    }

    /**
     * @param \EzSystems\EzPlatformContentForms\Data\Content\ContentCreateData $contentCreateData
     * @param string $languageCode
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    private function resolveContentCreateForm(
        ContentCreateData $contentCreateData,
        string $languageCode,
        bool $autosaveEnabled = true
    ): FormInterface {
        return $this->formFactory->create(ContentEditType::class, $contentCreateData, [
            'languageCode' => $languageCode,
            'mainLanguageCode' => $languageCode,
            'contentCreateStruct' => $contentCreateData,
            'drafts_enabled' => true,
            'autosave_enabled' => $autosaveEnabled,
        ]);
    }
}
