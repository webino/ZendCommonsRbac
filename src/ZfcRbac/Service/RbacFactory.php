<?php

namespace ZfcRbac\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class RbacFactory
 */
class RbacFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sl)
    {
        $config = $sl->get('Configuration');
        $config = $config['zfcrbac'];

        $rbac    = new Rbac($config);
        $options = $rbac->getOptions();

        foreach ($options->getProviders() as $class => $config) {
            $rbac->addProvider($class::factory($sl, $config));
        }

        foreach ($options->getFirewalls() as $class => $config) {
            $rbac->addFirewall(new $class($config));
        }

        return $rbac;
    }
}
