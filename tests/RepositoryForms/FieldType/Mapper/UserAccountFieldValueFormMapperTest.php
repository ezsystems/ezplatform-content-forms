<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformContentForms\Tests\FieldType\Mapper;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use EzSystems\EzPlatformContentForms\Data\User\UserCreateData;
use EzSystems\EzPlatformContentForms\FieldType\Mapper\UserAccountFieldValueFormMapper;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;

class UserAccountFieldValueFormMapperTest extends BaseMapperTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $data = new UserCreateData();
        $data->contentType = $this->createMock(ContentType::class);

        $formRoot = $this->getMockBuilder(FormInterface::class)->getMock();
        $formRoot
            ->method('getData')
            ->willReturn($data);

        $userEditForm = $this->getMockBuilder(FormInterface::class)->getMock();
        $config = $this->getMockBuilder(FormConfigInterface::class)->getMock();

        $config->method('getOption')
            ->with('intent')
            ->willReturn('update');
        $formRoot->method('getConfig')
            ->willReturn($config);
        $userEditForm->method('getRoot')
            ->willReturn($formRoot);

        $this->fieldForm->method('getRoot')
            ->willReturn($userEditForm);
    }

    public function testMapFieldValueFormNoLanguageCode()
    {
        $mapper = new UserAccountFieldValueFormMapper();

        $fieldDefinition = new FieldDefinition(['names' => []]);

        $this->data->expects($this->once())
            ->method('__get')
            ->with('fieldDefinition')
            ->willReturn($fieldDefinition);

        $this->config
            ->method('getOption')
            ->willReturnMap([
                ['languageCode', null, 'eng-GB'],
                ['mainLanguageCode', null, 'eng-GB'],
            ]);

        $mapper->mapFieldValueForm($this->fieldForm, $this->data);
    }

    public function testMapFieldValueFormWithLanguageCode()
    {
        $mapper = new UserAccountFieldValueFormMapper();

        $fieldDefinition = new FieldDefinition(['names' => ['eng-GB' => 'foo']]);

        $this->data->expects($this->once())
            ->method('__get')
            ->with('fieldDefinition')
            ->willReturn($fieldDefinition);

        $this->config
            ->method('getOption')
            ->with('languageCode')
            ->willReturn('eng-GB');

        $mapper->mapFieldValueForm($this->fieldForm, $this->data);
    }
}
