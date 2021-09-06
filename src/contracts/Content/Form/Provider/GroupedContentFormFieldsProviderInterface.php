<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Contracts\ContentForms\Content\Form\Provider;

interface GroupedContentFormFieldsProviderInterface
{
    /**
     * @param \Symfony\Component\Form\FormInterface[] $fieldsDataForm
     * @phpstan-return array<string, array<int, string>> Array of fieldGroupIdentifier grouped by fieldGroupName.
     */
    public function getGroupedFields(array $fieldsDataForm): array;
}

class_alias(GroupedContentFormFieldsProviderInterface::class, 'EzSystems\EzPlatformContentForms\Content\Form\Provider\GroupedContentFormFieldsProviderInterface');
