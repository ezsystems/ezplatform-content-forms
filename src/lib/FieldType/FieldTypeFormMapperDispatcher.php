<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformContentForms\FieldType;

use EzSystems\EzPlatformContentForms\Data\Content\FieldData;
use Symfony\Component\Form\FormInterface;

/**
 * FieldType mappers dispatcher.
 *
 * Adds the form elements matching the given Field Data Definition to a given Form.
 */
class FieldTypeFormMapperDispatcher implements FieldTypeFormMapperDispatcherInterface
{
    /**
     * FieldType form mappers, indexed by FieldType identifier.
     *
     * @var \EzSystems\EzPlatformContentForms\FieldType\FieldValueFormMapperInterface[]
     */
    private $mappers;

    /**
     * FieldTypeFormMapperDispatcher constructor.
     *
     * @param \EzSystems\EzPlatformContentForms\FieldType\FieldValueFormMapperInterface[] $mappers
     */
    public function __construct(array $mappers = [])
    {
        $this->mappers = $mappers;
    }

    public function addMapper(FieldValueFormMapperInterface $mapper, string $fieldTypeIdentifier): void
    {
        $this->mappers[$fieldTypeIdentifier] = $mapper;
    }

    public function map(FormInterface $fieldForm, FieldData $data): void
    {
        $fieldTypeIdentifier = $data->getFieldTypeIdentifier();

        if (!isset($this->mappers[$fieldTypeIdentifier])) {
            return;
        }

        $this->mappers[$fieldTypeIdentifier]->mapFieldValueForm($fieldForm, $data);
    }
}
