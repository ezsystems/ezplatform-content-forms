<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformContentForms\Content\View;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;

/**
 * A view that contains a Content.
 */
interface ContentTypeValueView
{
    /**
     * Returns the ContentType.
     *
     * @return ContentType
     */
    public function getContentType(): ContentType;
}
