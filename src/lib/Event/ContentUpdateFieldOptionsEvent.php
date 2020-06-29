<?php

namespace EzSystems\EzPlatformContentForms\Event;

use eZ\Publish\API\Repository\Values\Content\Content;
use EzSystems\EzPlatformContentForms\Data\Content\ContentUpdateData;
use EzSystems\EzPlatformContentForms\Data\Content\FieldData;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class ContentUpdateFieldOptionsEvent extends Event
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Content */
    private $content;

    /** @var \EzSystems\EzPlatformContentForms\Data\Content\ContentUpdateData */
    private $contentUpdateData;

    /** @var \Symfony\Component\Form\FormInterface */
    private $parentForm;

    /** @var \EzSystems\EzPlatformContentForms\Data\Content\FieldData */
    private $fieldData;

    /** @var array */
    private $options;

    public function __construct(
        Content $content,
        ContentUpdateData $contentUpdateData,
        FormInterface $parentForm,
        FieldData $fieldData,
        array $options
    ) {
        $this->content = $content;
        $this->contentUpdateData = $contentUpdateData;
        $this->parentForm = $parentForm;
        $this->fieldData = $fieldData;
        $this->options = $options;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function getContentUpdateData(): ContentUpdateData
    {
        return $this->contentUpdateData;
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
