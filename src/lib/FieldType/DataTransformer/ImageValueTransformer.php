<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformContentForms\FieldType\DataTransformer;

use eZ\Publish\Core\FieldType\Image\Value;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Data transformer for ezimage field type.
 *
 * {@inheritdoc}
 */
class ImageValueTransformer extends AbstractBinaryBaseTransformer implements DataTransformerInterface
{
    /**
     * @param Value $value
     *
     * @return array
     */
    public function transform($value)
    {
        if (null === $value) {
            $value = $this->fieldType->getEmptyValue();
        }

        return array_merge(
            $this->getDefaultProperties(),
            [
                'alternativeText' => $value->alternativeText,
                'additionalData' => $value->additionalData,
            ]
        );
    }

    /**
     * @param array $value
     *
     * @return Value
     *
     * @throws TransformationFailedException
     */
    public function reverseTransform($value)
    {
        /** @var Value $valueObject */
        $valueObject = $this->getReverseTransformedValue($value);

        if ($this->fieldType->isEmptyValue($valueObject)) {
            return $valueObject;
        }

        $valueObject->alternativeText = $value['alternativeText'];
        $valueObject->additionalData = $value['additionalData'];

        return $valueObject;
    }
}
