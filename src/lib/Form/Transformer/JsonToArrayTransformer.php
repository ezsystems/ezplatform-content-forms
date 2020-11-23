<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformContentForms\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

final class JsonToArrayTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        if ($value === null) {
            return '';
        }

        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    public function reverseTransform($value)
    {
        return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
    }
}
