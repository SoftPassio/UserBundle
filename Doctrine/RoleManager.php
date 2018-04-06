<?php

namespace AppVerk\UserBundle\Doctrine;

use AppVerk\Components\Doctrine\AbstractManager;
use AppVerk\Components\Model\RoleInterface;
use AppVerk\Components\Model\UserInterface;

class RoleManager extends AbstractManager
{
    public function createRole($name, $credentials)
    {
        /** @var RoleInterface $role */
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
        $this->persistAndFlash($role);
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
