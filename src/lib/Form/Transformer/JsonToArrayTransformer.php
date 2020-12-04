<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformContentForms\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

final class JsonToArrayTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        if ($value === null) {
            return '';
        }

        try {
            $encoded = json_encode((object) $value, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new TransformationFailedException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

        return $encoded;
    }

    public function reverseTransform($value)
    {
        if ($value === null) {
            return [];
        }
        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new TransformationFailedException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }
        return $decoded;
    }
}
