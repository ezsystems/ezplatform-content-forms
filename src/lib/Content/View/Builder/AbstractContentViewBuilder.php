<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformContentForms\Content\View\Builder;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Helper\FieldsGroups\FieldsGroupsList;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use eZ\Publish\Core\MVC\Symfony\View\Configurator;
use eZ\Publish\Core\MVC\Symfony\View\ParametersInjector;
use EzSystems\EzPlatformContentForms\Form\ActionDispatcher\ActionDispatcherInterface;
use Symfony\Component\Form\Form;

/*
 * @internal
 */
abstract class AbstractContentViewBuilder
{
    /** @var \eZ\Publish\API\Repository\Repository */
    protected $repository;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\Configurator */
    protected $viewConfigurator;

    /** @var \eZ\Publish\Core\MVC\Symfony\View\ParametersInjector */
    protected $viewParametersInjector;

    /** @var \EzSystems\EzPlatformContentForms\Form\ActionDispatcher\ActionDispatcherInterface */
    protected $contentActionDispatcher;

    /** @var \eZ\Publish\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface */
    protected $languagePreferenceProvider;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    protected $configResolver;

    /** @var \eZ\Publish\Core\Helper\FieldsGroups\FieldsGroupsList */
    private $fieldsGroupsList;

    /** @var \eZ\Publish\API\Repository\ContentService */
    protected $contentService;

    public function __construct(
        Repository $repository,
        Configurator $viewConfigurator,
        ParametersInjector $viewParametersInjector,
        ActionDispatcherInterface $contentActionDispatcher,
        UserLanguagePreferenceProviderInterface $languagePreferenceProvider,
        ConfigResolverInterface $configResolver,
        FieldsGroupsList $fieldsGroupsList,
        ContentService $contentService
    ) {
        $this->repository = $repository;
        $this->viewConfigurator = $viewConfigurator;
        $this->viewParametersInjector = $viewParametersInjector;
        $this->contentActionDispatcher = $contentActionDispatcher;
        $this->languagePreferenceProvider = $languagePreferenceProvider;
        $this->configResolver = $configResolver;
        $this->fieldsGroupsList = $fieldsGroupsList;
        $this->contentService = $contentService;
    }

    /**
     * Loads a visible Location.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    protected function loadLocation(int $locationId): Location
    {
        return $this->repository->getLocationService()->loadLocation($locationId);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    private function loadLanguage(string $languageCode): Language
    {
        return $this->repository->getContentLanguageService()->loadLanguage($languageCode);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    protected function resolveLanguage(array $parameters): Language
    {
        if (isset($parameters['languageCode'])) {
            return $this->loadLanguage($parameters['languageCode']);
        }

        if (isset($parameters['language'])) {
            if (is_string($parameters['language'])) {
                // @todo BC: route parameter should be called languageCode but it won't happen until 3.0
                return $this->loadLanguage($parameters['language']);
            }

            return $parameters['language'];
        }

        throw new InvalidArgumentException('Language',
            'No language information provided. Are you missing language or languageCode parameters?');
    }

    protected function getGroupedFields(Form $form): array
    {
        $fieldsDataForm = $form->get('fieldsData');
        $groupedFields = [];

        /** @var \Symfony\Component\Form\Form $fieldForm */
        foreach ($fieldsDataForm as $fieldForm) {
            /** @var \EzSystems\EzPlatformContentForms\Data\Content\FieldData $fieldData */
            $fieldData = $fieldForm->getViewData();

            $fieldGroup = $this->fieldsGroupsList->getFieldGroup(
                $fieldData->fieldDefinition
            );

            $groupedFields[$fieldGroup][] = $fieldForm->getName();
        }

        return $this->sortGroupedFields($groupedFields);
    }

    /**
     * Makes sure fields groups order in the same like in YAML definition.
     */
    private function sortGroupedFields(array $groupedFields): array
    {
        $groupedFieldsList = [];

        $fieldsGroups = $this->fieldsGroupsList->getGroups();
        foreach ($fieldsGroups as $fieldGroupIdentifier => $fieldGroupName) {
            if (array_key_exists($fieldGroupIdentifier, $groupedFields)) {
                $groupedFieldsList[$fieldGroupName] = $groupedFields[$fieldGroupIdentifier];
            }
        }

        return $groupedFieldsList;
    }
}
