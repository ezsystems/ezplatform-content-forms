<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformContentForms\Event;

use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use EzSystems\EzPlatformContentForms\Data\Content\FieldData;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class ContentCreateFieldOptionsEvent extends Event
{
    /** @var \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct */
    private $contentCreateStruct;

    /** @var \Symfony\Component\Form\FormInterface */
    private $parentForm;

    /** @var \EzSystems\EzPlatformContentForms\Data\Content\FieldData */
    private $fieldData;

    /** @var array */
    private $options;

    public function __construct(
        ContentCreateStruct $contentCreateStruct,
        FormInterface $parentForm,
        FieldData $fieldData,
        array $options
    ) {
        $this->contentCreateStruct = $contentCreateStruct;
        $this->parentForm = $parentForm;
        $this->fieldData = $fieldData;
        $this->options = $options;
    }

    public function getContentCreateStruct(): ContentCreateStruct
    {
        return $this->contentCreateStruct;
    }

    public function getParentForm(): FormInterface
    {
        return $this->parentForm;
    }

    public function getFieldData(): FieldData
    {
        return $this->fieldData;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function setOption(string $option, $value): void
    {
        $this->options[$option] = $value;
    }

    public function getOption(string $option)
    {
        return $this->options[$option] ?? null;
    }
}
