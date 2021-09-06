<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Tests\ContentForms\Content\Form\Provider;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\FieldType\TextLine\Value;
use eZ\Publish\Core\Helper\FieldsGroups\FieldsGroupsList;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use EzSystems\EzPlatformContentForms\Data\Content\FieldData;
use Ibexa\ContentForms\Content\Form\Provider\GroupedContentFormFieldsProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;

class GroupedContentFormFieldsProviderTest extends TestCase
{
    public function testGetGroupedFields()
    {
        $fieldsGroupsListMock = $this->getMockBuilder(FieldsGroupsList::class)->getMock();
        $fieldsGroupsListMock
            ->expects($this->at(0))
            ->method('getFieldGroup')
            ->withAnyParameters()
            ->willReturn('group_1');

        $fieldsGroupsListMock
            ->expects($this->at(1))
            ->method('getFieldGroup')
            ->withAnyParameters()
            ->willReturn('group_2');

        $fieldsGroupsListMock
            ->expects($this->at(2))
            ->method('getFieldGroup')
            ->withAnyParameters()
            ->willReturn('group_2');

        $fieldsGroupsListMock
            ->expects($this->once())
            ->method('getGroups')
            ->withAnyParameters()
            ->willReturn([
                'group_1' => 'Group 1',
                'group_2' => 'Group 2',
            ]);

        $subject = new GroupedContentFormFieldsProvider($fieldsGroupsListMock);


        $form1 = $this->getFormMockWithFieldData(
            'first_field',
            'first_field_type',
        );

        $form2 = $this->getFormMockWithFieldData(
            'second_field',
            'second_field_type',
        );

        $form3 = $this->getFormMockWithFieldData(
            'third_field',
            'third_field_type',
        );

        $result = $subject->getGroupedFields([$form1, $form2, $form3]);

        $expected = [
            "Group 1" => [
                0 => "first_field",
            ],
            "Group 2" => [
                0 => "second_field",
                1 => "third_field",
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    private function getFormMockWithFieldData(
        string $fieldDefIdentifier,
        string $fieldTypeIdentifier
    ) {
        $formMock = $this
            ->getMockBuilder(FormInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $formMock
            ->expects($this->once())
            ->method('getViewData')
            ->willReturn(new FieldData([
                'field' => new Field(['fieldDefIdentifier' => $fieldDefIdentifier]),
                'fieldDefinition' => new FieldDefinition(['fieldTypeIdentifier' => $fieldTypeIdentifier]),
                'value' => new Value('value'),
            ]));
        $formMock
            ->expects($this->once())
            ->method('getName')
            ->willReturn($fieldDefIdentifier);

        return $formMock;
    }
}
