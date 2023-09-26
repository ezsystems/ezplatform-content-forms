<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformContentForms\Data\Mapper;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ValueObject;
use EzSystems\EzPlatformContentForms\Data\Content\ContentUpdateData;
use EzSystems\EzPlatformContentForms\Data\Content\FieldData;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentUpdateMapper implements FormDataMapperInterface
{
    /**
     * Maps a ValueObject from eZ content repository to a data usable as underlying form data (e.g. create/update struct).
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content|\eZ\Publish\API\Repository\Values\ValueObject $contentDraft
     * @param array $params
     *
     * @return \EzSystems\EzPlatformContentForms\Data\Content\ContentUpdateData
     */
    public function mapToFormData(ValueObject $contentDraft, array $params = [])
    {
        $optionsResolver = new OptionsResolver();
        $this->configureOptions($optionsResolver);

        $params = $optionsResolver->resolve($params);
        $languageCode = $params['languageCode'];
        $currentFields = $params['currentFields'];
        $mappedCurrentFields = array_column($currentFields, null, 'fieldDefIdentifier');

        $data = new ContentUpdateData(['contentDraft' => $contentDraft]);
        $data->initialLanguageCode = $languageCode;

        $fields = $contentDraft->getFieldsByLanguage($languageCode);
        $mainLanguageCode = $contentDraft->getVersionInfo()->getContentInfo()->getMainLanguage()->getLanguageCode();

        foreach ($params['contentType']->fieldDefinitions as $fieldDef) {
            $isNonTranslatable = $fieldDef->isTranslatable === false;
            $field = $fields[$fieldDef->identifier];
            $shouldUseCurrentFieldValue = $isNonTranslatable
                && isset($mappedCurrentFields[$fieldDef->identifier])
                && $mainLanguageCode !== $languageCode;
            $data->addFieldData(new FieldData([
                'fieldDefinition' => $fieldDef,
                'field' => $field,
                'value' => $shouldUseCurrentFieldValue
                        ? $mappedCurrentFields[$fieldDef->identifier]->value
                        : $field->value,
            ]));
        }

        return $data;
    }

    private function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver
            ->setRequired(['languageCode', 'contentType', 'currentFields'])
            ->setAllowedTypes('contentType', ContentType::class)
            ->setDefault('currentFields', []);
    }
}
