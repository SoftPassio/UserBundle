<?php

namespace AppVerk\UserBundle\Doctrine;

use AppVerk\Components\Doctrine\AbstractManager;
use AppVerk\Components\Doctrine\UserManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use AppVerk\Components\Model\UserInterface;

class UserManager extends AbstractManager implements UserManagerInterface
{
    /**
     * @var UserPasswordEncoder
     */
    private $encoder;

    public function createUser($username, $email, $password, $role)
    {
        $user = $this->baseCreateUser($username, $email, $password, $role);

        $this->persistAndFlash($user);

        return true;
    }

    public function setEncoder(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function generateSalt()
    {
        return base64_encode(random_bytes(30));
    }

    protected function baseCreateUser($username, $email, $password, $role)
    {
        /** @var UserInterface $user */
        $user = new $this->className();
        $user->setSalt($this->generateSalt());
        $encodedPassword = $this->encodePassword($user, $password);

        $user->setPassword($encodedPassword);
        $user->addRole($role);
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setEnabled(true);

        return $user;
    }

    public function updateUser(UserInterface $user)
    {
        $this->objectManager->persist($user);
        $this->objectManager->flush();
    }

    public function encodePassword(UserInterface $user, $password)
    {
        return $this->encoder->encodePassword($user, $password);
    }

    public function removeUser(UserInterface $user)
    {
        $user = $this->softRemove($user);
        $this->remove($user);
    }

    protected function softRemove(UserInterface $user)
    {
        $rand = random_int(9, 99999);
        $user->setEnabled(false);
        $user->setEmail('remove_'.$rand.'_'.$user->getEmail());
        $user->setUsername('remove_'.$rand.'_'.$user->getUsername());

        $this->objectManager->persist($user);
        $this->objectManager->flush();

        return $user;
    }

    public function findUserByEmail($email)
    {
        /** @var UserInterface $user */
        $user = $this->getRepository()->findOneBy(['email' => $email, 'enabled' => true]);
        return $user;
    }

    public function findUserByUsername($username)
    {
        /** @var UserInterface $user */
        $user = $this->getRepository()->findOneBy(['username' => $username, 'enabled' => true]);
        return $user;
    }

    public function findUserByPassword($password)
    {
        /** @var UserInterface $user */
        $user = $this->getRepository()->findOneBy(['password' => $password]);
        return $user;
    }

    public function getUser($id)
    {
        /** @var UserInterface $user */
        $user = $this->getRepository()->find($id);
        return $user;
    }

    public function getUserByToken($token)
    {
        if(!$token){
            return null;
        }

        /** @var UserInterface $user */
        $user = $this->getRepository()->findOneBy([
            'passwordRequestToken' => $token,
        ]);

        if(!$user || !$user->isPasswordRequestNonExpired()){
            return null;
        }

        return $user;
    }
}
