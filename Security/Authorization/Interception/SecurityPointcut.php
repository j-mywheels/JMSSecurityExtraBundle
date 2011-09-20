<?php

namespace JMS\SecurityExtraBundle\Security\Authorization\Interception;

use CG\Core\ClassUtils;
use Metadata\MetadataFactoryInterface;
use JMS\AopBundle\Aop\PointcutInterface;

class SecurityPointcut implements PointcutInterface
{
    private $metadataFactory;
    private $secureAllServices;
    private $securedClasses = array();

    public function __construct(MetadataFactoryInterface $metadataFactory, $secureAllServices = false)
    {
        $this->metadataFactory = $metadataFactory;
        $this->secureAllServices = $secureAllServices;
    }

    public function setSecuredClasses(array $classes)
    {
        $this->securedClasses = $classes;
    }

    public function matchesClass(\ReflectionClass $class)
    {
        if ($this->secureAllServices) {
            return true;
        }

        if ('Controller' === substr(ClassUtils::getUserClass($class->name), -10)) {
            return true;
        }

        foreach ($this->securedClasses as $securedClass) {
            if ($class->name === $securedClass || $class->isSubclassOf($securedClass)) {
                return true;
            }
        }

        return false;
    }

    public function matchesMethod(\ReflectionMethod $method)
    {
        $userClass = ClassUtils::getUserClass($method->class);
        $metadata = $this->metadataFactory->getMetadataForClass($userClass);

        if (null === $metadata) {
            return false;
        }

        return isset($metadata->methodMetadata[$method->name]);
    }
}