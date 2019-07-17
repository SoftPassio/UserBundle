SoftPassio UserBundle documentation v2.0
=======================================

Simple and lightweight User bundle for Symfony 3 projects. Provides user and
role functionalities with ACL support.

Old version:
-------------
If u need help with 1.* branch, visit [v1.x documentation](https://github.com/SoftPassio/UserBundle/tree/v1).

Installation:
-------------

Required the bundle with composer:

~~~~ {.sourceCode .}
$ php composer.phar require soft-passio/user-bundle
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
        new SoftPassio\UserBundle\UserBundle(),
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
        access_denied_path: #route bame where user should be redirect when he dont have privileges to action

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

use SoftPassio\UserBundle\Entity\User as AbstractUser;
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
        access_denied_path: #route name
~~~~

Use annotation to define protected action

```php
// ./src/AppBundle/Controller/DefaultController.php

...
use SoftPassio\UserBundle\Annotation\AVSecurity;
...

    /**
     * ...
     * @AVSecurity(allow={"ROLE_ADMIN"}, disallow={"ROLE_X"}, name="list", group="default")
     */
    public function listAction()
    {
        return $this->render('@App/controller/user/list.html.twig');
    }
    
```

Custom AccessResolver
---
In some cases u need to create your own logic to decide about access to action. In that case u just need to create custom accessResolver and put your logic

```php
// ./src/AppBundle/Security/CustomAccessResolver.php

...
use SoftPassio\UserBundle\Security\AccessResolverInterface;
...

class SimpleAccessResolver implements AccessResolverInterface
{
    public function resolve(RoleableInterface $user, $action): bool
    {
    // your own logic
    }
}
```

Insert new resolver to configuration file:
~~~~ {.sourceCode .yaml}
#./app/config/user.yml

 user:
     entities:
        user_class: #E.g. AppBundle\Entity\User

     acl: 
        enabled:       #true|false defines to use or not to use ACL
        access_denied_path: #route bame where user should be redirect when he dont have privileges to action
        access_resolver_class: AppBundle\Security\CustomAccessResolver
~~~~
