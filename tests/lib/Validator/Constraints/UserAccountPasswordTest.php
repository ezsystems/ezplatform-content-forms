<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformContentForms\Tests\Validator\Constraints;

use EzSystems\EzPlatformContentForms\Validator\Constraints\UserAccountPassword;
use EzSystems\EzPlatformContentForms\Validator\Constraints\UserAccountPasswordValidator;
use PHPUnit\Framework\TestCase;

class UserAccountPasswordTest extends TestCase
{
    /** @var \EzSystems\EzPlatformContentForms\Validator\Constraints\Password */
    private $constraint;

    protected function setUp(): void
    {
        $this->constraint = new UserAccountPassword();
    }

    public function testConstruct()
    {
        $this->assertSame('ez.user.password.invalid', $this->constraint->message);
    }

    public function testValidatedBy()
    {
        $this->assertSame(UserAccountPasswordValidator::class, $this->constraint->validatedBy());
    }

    public function testGetTargets()
    {
        $this->assertSame([UserAccountPassword::CLASS_CONSTRAINT, UserAccountPassword::PROPERTY_CONSTRAINT], $this->constraint->getTargets());
    }
}
