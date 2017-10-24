<?php

namespace AppVerk\UserBundle\Command;

use AppVerk\UserBundle\Doctrine\RoleManager;
use AppVerk\UserBundle\Doctrine\UserManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use AppVerk\UserBundle\Entity\User;

class CreateAdminCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('user:create:admin')
            ->setDescription('Creates admin user')
            ->addArgument('username', InputArgument::REQUIRED)
            ->addArgument('email', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::REQUIRED)
            ->setHelp(
                <<<EOT
Help

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');

        /** * @var UserManager $userManager */
        $userManager = $this->getContainer()->get(UserManager::class);
        /** * @var RoleManager $roleManager */
        $roleManager = $this->getContainer()->get(RoleManager::class);

        $user = $userManager->findUserByUsername($username);

        if ($user instanceof User) {
            throw new \Exception("User ".$username." already exists!");
        }

        $userByEmail = $userManager->findUserByEmail($email);
        if ($userByEmail instanceof User) {
            throw new \Exception("User email: ".$email." already exists!");
        }
        $adminRole = $roleManager->findRoleByName('ROLE_ADMIN');
        if (!$adminRole) {
            $adminRole = $roleManager->createRole('ROLE_ADMIN', []);
        }

        $status = $userManager->createUser($username, $email, $password, $adminRole);

        $io = new SymfonyStyle($input, $output);
        if ($status === true) {
            $io->success("User '".$username."' was successful created!");
        } else {
            $io->error($status);
        }
    }
}
