<?php

namespace AppVerk\UserBundle\Doctrine;

use AppVerk\UserBundle\Model\UserInterface;
use AppVerk\UserBundle\Component\Doctrine\AbstractManager;
use AppVerk\UserBundle\Model\RoleInterface;
use Doctrine\ORM\EntityRepository;

class RoleManager extends AbstractManager
{
    /**
     * @return EntityRepository
     */
    public function getRepository() : EntityRepository
    {
        $aclRepository = $this->objectManager->getRepository($this->className);

        return $aclRepository;
    }

    public function createRole($name, $credentials)
    {
        $role = new $this->className();
        $role->setName($name);
        $role->setCredentials($credentials);

        $this->objectManager->persist($role);
        $this->objectManager->flush();

        return $role;
    }

    public function findRoleByName($name)
    {
        return $this->getRepository()->findOneBy(['name' => $name]);
    }

    public function updateRole(RoleInterface $role)
    {
        $this->objectManager->persist($role);
        $this->objectManager->flush();
    }

    public function removeRole(RoleInterface $role)
    {
        $users = $role->getUsers();
        /** @var UserInterface $user */
        foreach ($users as $user) {
            $user->setRole(null);
            $this->objectManager->persist($user);
        }
        $this->objectManager->remove($role);
        $this->objectManager->flush();
    }

    public function getRoles()
    {
        return $this->getRepository()->findAll();
    }
}
