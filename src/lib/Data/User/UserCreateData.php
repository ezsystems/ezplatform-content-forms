<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformContentForms\Data\User;

use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\Core\Repository\Values\User\UserCreateStruct;
use EzSystems\EzPlatformContentForms\Data\Content\ContentData;
use EzSystems\EzPlatformContentForms\Data\Content\FieldData;
use EzSystems\EzPlatformContentForms\Data\NewnessCheckable;

/**
 * @property FieldData[] $fieldsData
 */
class UserCreateData extends UserCreateStruct implements NewnessCheckable
{
    use ContentData;

    /**
     * @var UserGroup[]
     */
    private $parentGroups;

    public function isNew()
    {
        return true;
    }

    /**
     * @return UserGroup[]
     */
    public function getParentGroups()
    {
        return $this->parentGroups;
    }

    /**
     * Adds a parent group.
     *
     * @param UserGroup $parentGroup
     */
    public function addParentGroup(UserGroup $parentGroup)
    {
        $this->parentGroups[] = $parentGroup;
    }

    /**
     * @param UserGroup[] $parentGroups
     */
    public function setParentGroups(array $parentGroups)
    {
        $this->parentGroups = $parentGroups;
    }
}
