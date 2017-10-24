<?php

namespace UserBundle\Model;

interface UserInterface
{
    const ROLE_ADMIN = 'ROLE_ADMIN';

    /**
     * @return mixed
     */
    public function getId();

    public function __construct();

    /**
     * @return string
     */
    public function getUsername();

    /**
     * @param string $username
     */
    public function setUsername(string $username);

    /**
     * @return string
     */
    public function getFirstName();

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName);

    /**
     * @return string
     */
    public function getLastName();

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName);

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @param string $email
     */
    public function setEmail(string $email);

    /**
     * @return string
     */
    public function getPassword();

    /**
     * @param string $password
     */
    public function setPassword(string $password);

    /**
     * @return string
     */
    public function getSalt();

    /**
     * @param string $salt
     */
    public function setSalt(string $salt);

    /**
     * @return mixed
     */
    public function getRole();

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled);

    /**
     * @return mixed
     */
    public function getPasswordRequestedAt();

    /**
     * @param mixed $passwordRequestedAt
     */
    public function setPasswordRequestedAt($passwordRequestedAt);

    /**
     * @return mixed
     */
    public function getPasswordRequestToken();

    /**
     * @param mixed $passwordRequestToken
     */
    public function setPasswordRequestToken($passwordRequestToken);

    /**
     * @return mixed
     */
    public function getPhone();

    /**
     * @param mixed $phone
     */
    public function setPhone($phone);

    public function eraseCredentials();

    /**
     * {@inheritdoc}
     */
    public function isSuperAdmin(): bool;

    public function isPasswordRequestNonExpired(): bool;

    /**
     *
     * @return (string|boolean) The user role
     */
    public function getRoles();

    public function hasRole(string $role);

    public function setRole(RoleInterface $role = null);

    public function __toString();
}
