<?php
/**
 * This file is part of the eZ RepositoryForms package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace EzSystems\RepositoryForms\UserRegister;

use eZ\Publish\API\Repository\Repository;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Loads the registration user group from a configured, injected group ID.
 */
class ConfigurableRegistrationGroupLoader implements RegistrationGroupLoader
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var array
     */
    private $params = [];

    public function __construct(Repository $repository, $params = null)
    {
        $this->repository = $repository;
        $this->params = $params;
    }

    public function setParam($name, $value)
    {
        $this->params[$name] = $value;

        return $this;
    }

    public function loadGroup()
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->params = $resolver->resolve($this->params);

        return $this->repository->sudo(
            function () {
                return $this->repository->getUserService()->loadUserGroup(
                    $this->params['groupId']
                );
            }
        );
    }

    private function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setRequired('groupId');
    }
}
