<?php

namespace AppVerk\UserBundle\Doctrine;

use AppVerk\UserBundle\Component\AbstractManager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use AppVerk\UserBundle\Model\UserInterface;
use Doctrine\ORM\EntityRepository;

class UserManager extends AbstractManager
{
    /**
     * @var UserPasswordEncoder
     */
    private $encoder;

    public function __construct(string $className, ObjectManager $objectManager)
    {
        parent::__construct($className, $objectManager);
    }

    /**
     * @return EntityRepository
     */
    public function getRepository() : EntityRepository
    {
        $userRepository = $this->objectManager->getRepository($this->className);

        return $userRepository;
    }

    public function createUser($username, $email, $password, $role)
    {
        $user = $this->baseCreateUser($username, $email, $password, $role);

        $this->objectManager->persist($user);
        $this->objectManager->flush();

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

    private function baseCreateUser($username, $email, $password, $role)
    {
        $user = new $this->className();
        $user->setSalt($this->generateSalt());
        $encodedPassword = $this->encodePassword($user, $password);

        $user->setPassword($encodedPassword);
        $user->setRole($role);
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
        $this->objectManager->remove($user);
        $this->objectManager->flush();
    }

    private function softRemove(UserInterface $user): UserInterface
    {
        $rand = random_int(9, 99999);
        $user->setEnabled(false);
        $user->setEmail('remove_'.$rand.'_'.$user->getEmail());
        $user->setUsername('remove_'.$rand.'_'.$user->getUsername());

        $this->objectManager->persist($user);
        $this->objectManager->flush();

        return $user;
    }

    public function findUserByEmail(string $email)
    {
        return $this->getRepository()->findOneBy(['email' => $email, 'enabled' => true]);
    }

    public function findUserByUsername(string $username)
    {
        return $this->getRepository()->findOneBy(['username' => $username, 'enabled' => true]);
    }
}
