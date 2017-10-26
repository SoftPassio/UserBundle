<?php

namespace AppVerk\UserBundle\Util;

use AppVerk\UserBundle\Component\TokenGeneratorInterface;

class TokenGenerator implements TokenGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generateToken()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
