<?php

namespace AppVerk\UserBundle\Entity;

use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use AppVerk\UserBundle\Model\User as AbstractUser;

abstract class User extends AbstractUser implements \Serializable
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

    /**
     * {@inheritdoc}
     */
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

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return $this->enabled;
    }
}
