<?php

namespace UserBundle\Doctrine;

use AppBundle\Repository\RoleRepository;
use UserBundle\Model\UserInterface;
use Component\Doctrine\AbstractManager;
use UserBundle\Model\RoleInterface;

class RoleManager extends AbstractManager
{
    /**
     * @return RoleRepository
     */
    public function getRepository()
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

    public function getRolesQuery(array $filters = [])
    {
        return $this->getRepository()->getRolesQuery($filters);
    }

    public function getRolesCount()
    {
        return $this->getRepository()->getRolesCount();
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
