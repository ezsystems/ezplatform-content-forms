<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformContentForms\FieldType\DataTransformer;

use eZ\Publish\Core\FieldType\Time\Value;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * DataTransformer for Time\Value.
 */
class TimeValueTransformer implements DataTransformerInterface
{
    /**
     * @param \eZ\Publish\Core\FieldType\Time\Value $value
     *
     * @return int|null
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function transform($value)
    {
        if (!$value instanceof Value) {
            throw new TransformationFailedException(
                sprintf('Expected a %s', Value::class)
            );
        }

        if (null === $value->time) {
            return null;
        }

        return $value->time;
    }

    /**
     * @param int $value
     *
     * @return Value|null
     *
     * @throws TransformationFailedException
     */
    public function reverseTransform($value)
    {
        if (null === $value || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            throw new TransformationFailedException(
                sprintf('Expected a numeric, got %s instead', gettype($value))
            );
        }

        return new Value($value);
    }
}
