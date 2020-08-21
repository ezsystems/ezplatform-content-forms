<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformContentForms\Tests\FieldType\DataTransformer;

use eZ\Publish\Core\FieldType\RelationList\Value;
use EzSystems\EzPlatformContentForms\FieldType\DataTransformer\RelationListValueTransformer;
use PHPUnit\Framework\TestCase;

final class RelationListValueTransformerTest extends TestCase
{
    /**
     * @dataProvider dataProviderForTestReverseTransform
     */
    public function testReverseTransform($value, ?Value $expectedValue): void
    {
        $transformer = new RelationListValueTransformer();

        $this->assertEquals(
            $expectedValue,
            $transformer->reverseTransform($value)
        );
    }

    public function dataProviderForTestReverseTransform(): iterable
    {
        yield 'null' => [
            null,
            null,
        ];

        yield 'optimistic' => [
            '1,2,3,5,8,13',
            new Value([1, 2, 3, 5, 8, 13]),
        ];
    }
}
