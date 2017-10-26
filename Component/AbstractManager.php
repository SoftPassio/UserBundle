<?php

namespace AppVerk\UserBundle\Component;

use Doctrine\Common\Persistence\ObjectManager;

abstract class AbstractManager implements ManagerInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    protected $className;

    public function __construct(string $className, ObjectManager $objectManager)
    {
        $this->className = $className;
        $this->objectManager = $objectManager;
    }
}
