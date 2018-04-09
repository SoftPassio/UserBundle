<?php

namespace AppVerk\UserBundle\Model;

use AppVerk\Components\Model\RoleInterface;
use AppVerk\Components\Model\UserInterface;

abstract class Role implements RoleInterface
{
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $credentials;

    /**
     * @var array
     */
    protected $users;

    /**
     * @var boolean
     */
    protected $deletable;

    public function __construct()
    {
        $this->deletable = true;
        $this->credentials = '';
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * Set credentials
     *
     * @param array $credentials
     *
     * @return $this
     */
    public function setCredentials(array $credentials)
    {
        $this->credentials = serialize($credentials);

        return $this;
    }

    /**
     * Get credentials
     *
     * @return array
     */
    public function getCredentials()
    {
        return unserialize($this->credentials);
    }

    /**
     * Add user
     *
     */
    public function addUser($user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     * @param UserInterface $user
     * @return $this
     */
    public function removeUser(UserInterface $user)
    {
        if (false !== $key = array_search($user, $this->users, true)) {
            unset($this->users[$key]);
            $this->users = array_values($this->users);
        }

        return $this;
    }

    /**
     * Get users
     *
     * @return array
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @return bool
     */
    public function isDeletable(): bool
    {
        return $this->deletable;
    }

    /**
     * @param bool $deletable
     */
    public function setDeletable(bool $deletable)
    {
        $this->deletable = $deletable;
    }

    public function __toString()
    {
        return $this->name ? $this->name : '';
    }
}
