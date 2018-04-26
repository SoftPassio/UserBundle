<?php

namespace AppVerk\UserBundle\Model;

use AppVerk\Components\Model\UserInterface;
use DateTime;

abstract class User implements UserInterface
{
    protected $id;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $salt;

    /**
     * @var array
     */
    protected $roles;

    /**
     * @var boolean
     */
    protected $enabled;

    /**
     * @var \DateTime
     */
    protected $passwordRequestedAt;

    /**
     * @var string
     */
    protected $passwordRequestToken;

    /**
     * @var string
     */
    protected $phone;

    /**
     * @return mixed
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function __construct()
    {
        $this->enabled = false;
        $this->roles = [];
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(?string $firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(?string $lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getSalt(): string
    {
        return $this->salt;
    }

    /**
     * @param string $salt
     */
    public function setSalt(string $salt)
    {
        $this->salt = $salt;
    }

    /**
     * @return mixed
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param mixed $enabled
     */
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return mixed
     */
    public function getPasswordRequestedAt(): ?DateTime
    {
        return $this->passwordRequestedAt;
    }

    /**
     * @param mixed $passwordRequestedAt
     */
    public function setPasswordRequestedAt(DateTime $passwordRequestedAt)
    {
        $this->passwordRequestedAt = $passwordRequestedAt;
    }

    /**
     * @return mixed
     */
    public function getPasswordRequestToken(): ?string
    {
        return $this->passwordRequestToken;
    }

    /**
     * @param mixed $passwordRequestToken
     */
    public function setPasswordRequestToken(string $passwordRequestToken)
    {
        $this->passwordRequestToken = $passwordRequestToken;
    }

    /**
     * @return mixed
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone(string $phone)
    {
        $this->phone = $phone;
    }

    public function eraseCredentials()
    {
        return;
    }

    /**
     * Checks whether the user's account has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return bool true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return bool true if the user is not locked, false otherwise
     *
     * @see LockedException
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *
     * @return bool true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return bool true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled()
    {
        return (bool)$this->enabled;
    }

    public function isPasswordRequestNonExpired(): bool
    {
        return $this->getPasswordRequestedAt() instanceof \DateTime &&
            $this->getPasswordRequestedAt()->getTimestamp() + self::TOKEN_TTL > time();
    }

    public function hasRole(string $searchRole): bool
    {
        foreach ($this->roles as $role) {
            if ((string)$role === (string)$searchRole) {
                return true;
            }
        }

        return false;
    }

    public function setRoles(array $roles)
    {
        $this->roles = [];
        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * Set role
     *
     * @param string $newRole
     * @return User
     */
    public function addRole(string $newRole = null)
    {
        if ($this->hasRole($newRole)) {
            return $this;
        }
        $this->roles[] = (string)$newRole;

        return $this;
    }

    public function removeRole(string $role)
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    public function __toString()
    {
        if ($this->firstName && $this->lastName) {
            return $this->firstName.' '.$this->lastName;
        }

        return $this->username ? $this->username : '';
    }

    public function serialize()
    {
        return serialize(
            [
                $this->password,
                $this->username,
                $this->enabled,
                $this->id,
                $this->email,
            ]
        );
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        list(
            $this->password,
            $this->username,
            $this->enabled,
            $this->id,
            $this->email,
            ) = $data;
    }
}
