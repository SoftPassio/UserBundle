Appverk UserBundle documentation v1.0.2
=======================================

Simple and lightweight User bundle for Symfony 3.3 projects. Provides user and
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
         role_class: #E.g. AppBundle\Entity\Role

     acl_enabled: #true|false defines to use or not to use ACL

~~~~
Import user.yml file to config.yml

~~~~ {.sourceCode .yaml}
imports:
...
- { resource: user.yml }
~~~~

Next create two entities in your bundle (E.g. AppBundleEntity):

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
}
~~~~

-   Role

~~~~ {.sourceCode .php}
<?php

namespace AppBundle\Entity;

use AppVerk\UserBundle\Entity\Role as AbstractRole;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RoleRepository")
 */
class Role extends AbstractRole
{
    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="role")
     */
    protected $users;
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

If You enable ACL

~~~~ {.sourceCode .yaml}
#./app/config/user.yml

 user:
     acl_enabled: true

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

Last thing to do is to create and display form in Your administration
panel or any other place where You want to manage role credentials.

The form may look like this:

~~~~ {.sourceCode .php}
<?php

namespace ExampleBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use AppBundle\Entity\Role;
use AppVerk\UserBundle\Service\Acl\AclProvider;

class RoleType extends AbstractType
{
    /** @var AclProvider */
    private $aclProvider;

    public function __construct(AclProvider $aclProvider)
    {
        $this->aclProvider = $aclProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Role $role */
        $role = $builder->getForm()->getData();
        $credentials = $role->getCredentials();

        if (!$credentials) {
            $credentials = [];
        }
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'required'    => true
                ]
            );

        $aclChoices = $this->aclProvider->getAclForChoice();
        foreach ($aclChoices as $section => $roles) {
            $builder->add(
                'permissions',
                ChoiceType::class,
                [
                    'choices'       => $roles,
                    'label'         => false,
                    'required'      => true,
                    'expanded'      => true,
                    'multiple'      => true,
                    'mapped'        => false,
                    'data'          => $credentials,
                    'property_path' => 'permissions['.$section.']',
                ]
            );
        }

        $builder
            ->add(
                'submit',
                SubmitType::class
            );
    }
...
~~~~
