Appverk UserBundle documentation v2.0
=======================================

Simple and lightweight User bundle for Symfony 3 projects. Provides user and
role functionalities with ACL support.

Installation:
-------------

Required the bundle with composer:

~~~~ {.sourceCode .}
$ php composer.phar require app-verk/user-bundle
~~~~

Configuration
-------------

Register the bundle in your AppKernel.php

~~~~ {.sourceCode .php}
// ./app/AppKernel.php
public function registerBundles()
{
    $bundles = [
        ...
        new AppVerk\UserBundle\UserBundle(),
        ...
    ];
}
~~~~

Add a new config file, for example user.yml

~~~~ {.sourceCode .yaml}
#./app/config/user.yml

 user:
     entities:
        user_class: #E.g. AppBundle\Entity\User

     acl: 
        enabled:       #true|false defines to use or not to use ACL
        redirect_path: #path where user should be redirect when he dont have privileges to action

~~~~
Import user.yml file to config.yml

~~~~ {.sourceCode .yaml}
imports:
...
- { resource: user.yml }
~~~~

Next create two entities in your bundle (E.g. AppBundle\Entity):

-   User

~~~~ {.sourceCode .php}
<?php

namespace AppBundle\Entity;

use AppVerk\UserBundle\Entity\User as AbstractUser;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User extends AbstractUser implements EntityInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
}
~~~~

*You can use configuration format which you prefer (yml, xml, php or
annotation)*

Run 
~~~~
    php bin/console doctrine:schema:update --force
~~~~

Now You can create admin user with command line:
~~~~
    php bin/console user:create:admin <username> <email> <password>
~~~~

ACL
---

Enable ACL

~~~~ {.sourceCode .yaml}
#./app/config/user.yml

 user:
     acl
        enabled: true
        redirect_path: #routing path
~~~~

Put following acl.yml file in each bundle You want to control using ACL.
acl.yml file should be placed in Resources/config/ directory

~~~~ {.sourceCode .yaml}
name: #Bundle name or short description
controllers:
    ExampleController: #controller name
        - { action: indexAction, label: Main page access, group: Main page, section: Front }

excluded_controllers: #list of controllers and actions to which all users access will be granted
    ExampleController:
        - loginAction
~~~~
