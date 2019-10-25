<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformContentForms\Data\User;

use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use EzSystems\EzPlatformContentForms\Data\Content\ContentData;
use EzSystems\EzPlatformContentForms\Data\NewnessCheckable;

/**
 * @property \EzSystems\EzPlatformContentForms\Data\Content\FieldData[] $fieldsData
 * @property \eZ\Publish\API\Repository\Values\User\User $user
 */
class UserUpdateData extends UserUpdateStruct implements NewnessCheckable
{
    use ContentData;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\User
     */
    public $user;

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    public $contentType;

    public function isNew()
    {
        return false;
    }
}
