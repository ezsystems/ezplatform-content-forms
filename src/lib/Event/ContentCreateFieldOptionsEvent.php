<?php

namespace EzSystems\EzPlatformContentForms\Event;

use EzSystems\EzPlatformContentForms\Data\Content\ContentCreateData;
use EzSystems\EzPlatformContentForms\Data\Content\FieldData;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\EventDispatcher\Event;

final class ContentCreateFieldOptionsEvent extends Event
{
    /** @var \EzSystems\EzPlatformContentForms\Data\Content\ContentCreateData */
    private $contentCreateData;

    /** @var \Symfony\Component\Form\FormInterface */
    private $parentForm;

    /** @var \EzSystems\EzPlatformContentForms\Data\Content\FieldData */
    private $fieldData;

    /** @var array */
    private $options;

    public function __construct(
        ContentCreateData $contentCreateData,
        FormInterface $parentForm,
        FieldData $fieldData,
        array $options
    ) {
        $this->contentCreateData = $contentCreateData;
        $this->parentForm = $parentForm;
        $this->fieldData = $fieldData;
        $this->options = $options;
    }

    public function getContentCreateData(): ContentCreateData
    {
        return $this->contentCreateData;
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
