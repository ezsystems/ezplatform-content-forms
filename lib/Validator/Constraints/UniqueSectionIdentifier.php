<?php
/**
 * This file is part of the eZ RepositoryForms package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace EzSystems\RepositoryForms\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueSectionIdentifier extends Constraint
{
    /**
     * %identifier% placeholder is passed.
     *
     * @var string
     */
    public $message = 'ez.section.identifier.unique';

    public function validatedBy()
    {
        return 'ezrepoforms.validator.unique_section_identifier';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
