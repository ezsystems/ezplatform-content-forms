<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformContentForms\Content\View\Builder;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\MVC\Symfony\View\Builder\ViewBuilder;
use EzSystems\EzPlatformContentForms\Content\View\ContentCreateSuccessView;
use EzSystems\EzPlatformContentForms\Content\View\ContentCreateView;

/**
 * Builds ContentCreateView objects.
 *
 * @internal
 */
class ContentCreateViewBuilder extends AbstractContentViewBuilder implements ViewBuilder
{
    public function matches($argument)
    {
        return 'ez_content_edit:createWithoutDraftAction' === $argument;
    }

    /**
     * @param array $parameters
     *
     * @return \EzSystems\EzPlatformContentForms\Content\View\ContentCreateSuccessView|\EzSystems\EzPlatformContentForms\Content\View\ContentCreateView
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function buildView(array $parameters)
    {
        $view = new ContentCreateView($this->configResolver->getParameter('content_edit.templates.create'));

        $language = $this->resolveLanguage($parameters);
        $location = $this->resolveLocation($parameters);
        $contentType = $this->resolveContentType($parameters, $this->languagePreferenceProvider->getPreferredLanguages());
        /** @var \Symfony\Component\Form\Form $form */
        $form = $parameters['form'];

        if ($form->isSubmitted() && $form->isValid() && null !== $form->getClickedButton()) {
            $this->contentActionDispatcher->dispatchFormAction(
                $form,
                $form->getData(),
                $form->getClickedButton()->getName(),
                ['referrerLocation' => $location]
            );

            if ($response = $this->contentActionDispatcher->getResponse()) {
                $view = new ContentCreateSuccessView($response);
                $view->setLocation($location);

                return $view;
            }
        }

        $view->setContentType($contentType);
        $view->setLanguage($language);
        $view->setLocation($location);
        $view->setForm($form);

        $view->addParameters([
            'content_type' => $contentType,
            'language' => $language,
            'parent_location' => $location,
            'form' => $form->createView(),
            'grouped_fields' => $this->getGroupedFields($form),
        ]);

        $this->viewParametersInjector->injectViewParameters($view, $parameters);
        $this->viewConfigurator->configure($view);

        return $view;
    }

    /**
     * Loads ContentType with identifier $contentTypeIdentifier.
     *
     * @param string $contentTypeIdentifier
     * @param string[] $prioritizedLanguages
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    private function loadContentType(string $contentTypeIdentifier, array $prioritizedLanguages = []): ContentType
    {
        return $this->repository->getContentTypeService()->loadContentTypeByIdentifier(
            $contentTypeIdentifier,
            $prioritizedLanguages
        );
    }

    /**
     * @param array $parameters
     * @param array $languageCodes
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    private function resolveContentType(array $parameters, array $languageCodes): ContentType
    {
        if (isset($parameters['contentType'])) {
            return $parameters['contentType'];
        }

        if (isset($parameters['contentTypeIdentifier'])) {
            return $this->loadContentType($parameters['contentTypeIdentifier'], $languageCodes);
        }

        throw new InvalidArgumentException(
            'ContentType',
            'No Content Type could be loaded from the parameters'
        );
    }

    /**
     * @param array $parameters
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    private function resolveLocation(array $parameters): Location
    {
        if (isset($parameters['parentLocation'])) {
            return $parameters['parentLocation'];
        }

        if (isset($parameters['parentLocationId'])) {
            return $this->loadLocation((int) $parameters['parentLocationId']);
        }

        throw new InvalidArgumentException(
            'ParentLocation',
            'Unable to load parent Location from the parameters'
        );
    }
}
