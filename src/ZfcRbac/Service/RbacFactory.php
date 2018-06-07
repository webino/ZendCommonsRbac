<?php

namespace ZfcRbac\Service;

use RuntimeException;
use ZfcRbac\Provider\Event;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\Exception\ServiceNotFoundException;

/**
 * Class RbacFactory
 */
class RbacFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $services
     * @return Rbac
     */
    public function createService(ServiceLocatorInterface $services)
    {
        $config = $services->get('Configuration');
        $config = $config['zfcrbac'];

        $rbac    = new Rbac($config);
        $options = $rbac->getOptions();

        foreach ($options->getProviders() as $class => $config) {
            $rbac->addProvider($class::factory($services, $config));
        }

        foreach ($options->getFirewalls() as $class => $config) {
            $rbac->addFirewall(new $class($config));
        }

        $rbac->getEventManager()->attach(Event::EVENT_LOAD_IDENTITY, function () use ($services, $rbac) {
            $provider = $rbac->getOptions()->getIdentityProvider();
            if (!$services->has($provider)) {
                throw new RuntimeException(sprintf(
                    'An identity provider with the name "%s" does not exist',
                    $provider
                ));
            }
            try {
                $rbac->setIdentity($services->get($provider));
            } catch (ServiceNotFoundException $e) {
                throw new RuntimeException(sprintf(
                    'Unable to set your identity - are you sure the alias "%s" is correct?',
                    $provider
                ));
            }
        });

        return $rbac;
    }
}
