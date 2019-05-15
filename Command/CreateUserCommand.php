<?php

namespace AppVerk\UserBundle\Command;

use AppVerk\Components\Model\UserInterface;
use AppVerk\UserBundle\Doctrine\UserManager;
use AppVerk\UserBundle\Entity\User;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Command\Command;

class CreateUserCommand extends Command
{
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('appverk:user:create')
            ->setDescription('Creates user with ROLE_MASTER role')
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

        $user = $this->userManager->findUserByUsername($username);

        if ($user instanceof User) {
            throw new \Exception("User ".$username." already exists!");
        }

        $userByEmail = $this->userManager->findUserByEmail($email);
        if ($userByEmail instanceof UserInterface) {
            throw new \Exception("User email: ".$email." already exists!");
        }

        $status = $this->userManager->createUser($username, $email, $password, UserInterface::ROLE_MASTER);

        $io = new SymfonyStyle($input, $output);
        if ($status === true) {
            $io->success("User '".$username."' was successful created!");
        } else {
            $io->error($status);
        }
    }
}
