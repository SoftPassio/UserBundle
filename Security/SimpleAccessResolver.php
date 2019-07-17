<?php

namespace SoftPassio\UserBundle\Security;

use SoftPassio\UserBundle\Annotation\AVSecurity;
use SoftPassio\UserBundle\Entity\RoleableInterface;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\Reader;
use ReflectionClass;

class SimpleAccessResolver implements AccessResolverInterface
{
    const TYPE_ALLOW = 'allow';
    const TYPE_DISALLOW = 'disallow';

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var RoleableInterface
     */
    private $user;

    /**
     * @var ReflectionClass
     */
    private $reflectionClass;

    private $controllerParams;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function resolve(RoleableInterface $user, $action): bool
    {
        $controllerActionParams = explode("::", $action, 2);

        $this->controllerParams = $controllerActionParams;
        $this->user = $user;
        $this->reflectionClass = new ReflectionClass($this->controllerParams[0]);

        return $this->methodHaveAnnotation() ? $this->checkMethod() : $this->checkClass();
    }

    private function validateAnnnotation($securityAnnotation)
    {
        if (empty($securityAnnotation->allow) && empty($securityAnnotation->disallow)) {
            throw new AnnotationException("Set at least one method allow or disallow with access groups");
        }

        if (!empty($securityAnnotation->allow) && !empty($securityAnnotation->disallow)) {
            throw new AnnotationException(
                "Logical exception u cant at the same time set allow and disallow access for user groups"
            );
        }
    }

    private function methodHaveAnnotation()
    {
        $method = $this->reflectionClass->getMethod($this->controllerParams[1]);

        return $actionAnnotation = $this->reader->getMethodAnnotation(
            $method,
            AVSecurity::class
        );
    }

    private function checkMethod()
    {
        $method = $this->reflectionClass->getMethod($this->controllerParams[1]);

        $actionAnnotation = $this->reader->getMethodAnnotation(
            $method,
            AVSecurity::class
        );

        $this->validateAnnnotation($actionAnnotation);

        return (empty($actionAnnotation->allow)) ? $this->checkGroups($actionAnnotation->disallow, self::TYPE_DISALLOW)
            : $this->checkGroups($actionAnnotation->allow, self::TYPE_ALLOW);
    }

    private function checkClass()
    {
        $classAnnotation = $this->reader->getClassAnnotation(
            $this->reflectionClass,
            AVSecurity::class
        );

        if (!$classAnnotation) {
            return true;
        }

        $this->validateAnnnotation($classAnnotation);

        return (empty($classAnnotation->allow)) ? $this->checkGroups($classAnnotation->disallow, self::TYPE_DISALLOW)
            : $this->checkGroups($classAnnotation->allow, self::TYPE_ALLOW);
    }

    private function checkGroups(array $roles, string $type): bool
    {
        foreach ($roles as $role) {
            if ($this->user->hasRole($role)) {
                return $type === self::TYPE_ALLOW;
            }
        }

        return $type === self::TYPE_DISALLOW;
    }
}
