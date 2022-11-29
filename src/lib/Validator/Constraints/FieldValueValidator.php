<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformContentForms\Validator\Constraints;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\SPI\FieldType\Value;
use EzSystems\EzPlatformContentForms\Data\Content\FieldData;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Util\PropertyPath;

/**
 * Base class for field value validators.
 */
class FieldValueValidator extends FieldTypeValidator
{
    /**
     * @param \EzSystems\EzPlatformContentForms\Data\Content\FieldData $value
     * @param \Symfony\Component\Validator\Constraint $constraint
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof FieldData) {
            return;
        }

        $fieldValue = $this->getFieldValue($value);
        if (!$fieldValue) {
            return;
        }

        $fieldTypeIdentifier = $this->getFieldTypeIdentifier($value);
        $fieldDefinition = $this->getFieldDefinition($value);
        $fieldType = $this->fieldTypeService->getFieldType($fieldTypeIdentifier);

        if (
            false === $fieldDefinition->isTranslatable
            && isset($constraint->payload['intent'])
            && $constraint->payload['intent'] === 'translate'
        ) {
            // In content translation mode and the field is not translatable.
            // Validation has to be skipped or it'll violate constraints on
            // some field i.e. ezuser

            return;
        }

        if ($fieldDefinition->isRequired && $fieldType->isEmptyValue($fieldValue)) {
            $validationErrors = [
                new ValidationError(
                    "Value for required field definition '%identifier%' with language '%languageCode%' is empty",
                    null,
                    ['%identifier%' => $fieldDefinition->identifier, '%languageCode%' => $value->field->languageCode],
                    'empty'
                ),
            ];
        } else {
            $validationErrors = $fieldType->validateValue($fieldDefinition, $fieldValue);
        }

        $this->processValidationErrors($validationErrors);
    }

    /**
     * Returns the field value to validate.
     */
    protected function getFieldValue(FieldData $value): ?Value
    {
        return $value->value;
    }

    /**
     * Returns the field definition $value refers to.
     * FieldDefinition object is needed to validate field value against field settings.
     */
    protected function getFieldDefinition(FieldData $value): FieldDefinition
    {
        return $value->fieldDefinition;
    }

    /**
     * Returns the fieldTypeIdentifier for the field value to validate.
     *
     * @param FieldData|ValueObject $value fieldData ValueObject holding the field value to validate
     *
     * @return string
     */
    protected function getFieldTypeIdentifier(ValueObject $value): string
    {
        return $value->fieldDefinition->fieldTypeIdentifier;
    }

    protected function generatePropertyPath($errorIndex, $errorTarget): string
    {
        $basePath = 'value';

        return $errorTarget === null
            ? $basePath
            : PropertyPath::append($basePath, $errorTarget);
    }
}
