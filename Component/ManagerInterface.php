<?php

namespace AppVerk\UserBundle\Component;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

interface ManagerInterface
{
    public function __construct(string $className, ObjectManager $objectManager);

    /**
     * @return ObjectRepository
     */
    public function getRepository();

}
