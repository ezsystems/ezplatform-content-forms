<?php

return EzSystems\EzPlatformCodeStyle\PhpCsFixer\EzPlatformInternalConfigFactory::build()
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__ . '/src')
            ->exclude([
                'bin/.travis',
                'doc',
                'vendor',
            ])
            ->files()->name('*.php')
    )
;
