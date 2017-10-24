<?php

namespace AppVerk\UserBundle\Model;

interface RoleInterface
{
    const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     */
    public function setName(string $name);

    /**
     * @return array
     */
    public function getCredentials();

    /**
     * @param array $credentials
     */
    public function setCredentials(array $credentials);

    /**
     * Add user
     */
    public function addUser($user);

    /**
     * Remove user
     */
    public function removeUser($user);

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers();

    /**
     * @return bool
     */
    public function isDeletable(): bool;

    /**
     * @param bool $deletable
     */
    public function setDeletable(bool $deletable);
}
