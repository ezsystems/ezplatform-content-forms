<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzPlatformContentFormsBundle;

use EzSystems\EzPlatformContentFormsBundle\DependencyInjection\Compiler\FieldTypeFormMapperDispatcherPass;
use EzSystems\EzPlatformContentFormsBundle\DependencyInjection\Compiler\LimitationFormMapperPass;
use EzSystems\EzPlatformContentFormsBundle\DependencyInjection\Compiler\LimitationValueMapperPass;
use EzSystems\EzPlatformContentFormsBundle\DependencyInjection\Compiler\ViewBuilderRegistryPass;
use EzSystems\EzPlatformContentFormsBundle\DependencyInjection\Configuration\Parser\ContentCreateView;
use EzSystems\EzPlatformContentFormsBundle\DependencyInjection\Configuration\Parser\ContentEdit;
use EzSystems\EzPlatformContentFormsBundle\DependencyInjection\Configuration\Parser\ContentEditView;
use EzSystems\EzPlatformContentFormsBundle\DependencyInjection\Configuration\Parser\LimitationValueTemplates;
use EzSystems\EzPlatformContentFormsBundle\DependencyInjection\Configuration\Parser\UserEdit;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EzPlatformContentFormsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new FieldTypeFormMapperDispatcherPass());
        $container->addCompilerPass(new ViewBuilderRegistryPass());

        $eZExtension = $container->getExtension('ezpublish');
        $eZExtension->addConfigParser(new ContentEdit());
        $eZExtension->addConfigParser(new UserEdit());
        $eZExtension->addConfigParser(new ContentEditView());
        $eZExtension->addConfigParser(new ContentCreateView());
        $eZExtension->addDefaultSettings(__DIR__ . '/Resources/config', ['ezpublish_default_settings.yaml']);
    }
}
