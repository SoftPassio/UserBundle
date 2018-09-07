<?php

namespace AppVerk\UserBundle\Entity;

interface RoleableInterface
{
    public function hasRole($role);
}
