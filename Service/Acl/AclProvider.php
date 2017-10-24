<?php

namespace UserBundle\Service\Acl;

use Symfony\Component\Yaml\Yaml;
use UserBundle\Entity\Role;
use UserBundle\Entity\User;

class AclProvider
{
    private $aclConfig = [];

    private $aclEnabled;

    private $rootDir;

    private $excludedControllers = [];

    public function __construct($rootDir, $aclEnabled)
    {
        $this->rootDir = $rootDir;
        $this->aclEnabled = $aclEnabled;
        $this->buildAclConfig();
    }

    private function buildAclConfig()
    {
        $bundlesDirectory = $this->rootDir.'/../src/Bundle/';
        $bundles = array_diff(scandir($bundlesDirectory), ['..', '.']);

        foreach ($bundles as $bundle) {
            if (!is_dir($bundlesDirectory.$bundle)) {
                continue;
            }
            $configBundlePath = $bundlesDirectory.'/'.$bundle.'/Resources/config/acl.yml';
            if (file_exists($configBundlePath)) {
                $groupConfig = Yaml::parse(file_get_contents($configBundlePath));
                $config = [];
                if (!empty($groupConfig['controllers'])) {
                    $config = [
                        'controllers' => $groupConfig['controllers'],
                    ];
                }

                if (isset($groupConfig['excluded_controllers'])) {
                    $config['excluded_controllers'] = $groupConfig['excluded_controllers'];
                    $this->excludedControllers = array_merge_recursive(
                        $this->excludedControllers,
                        $config['excluded_controllers']
                    );
                }
                $this->aclConfig = array_merge_recursive($this->aclConfig, $config);
            }
        }

        if (empty($this->aclConfig)) {
            throw new \Exception('acl.yml file need to be configured');
        }
    }

    public function getAclConfig()
    {
        return $this->aclConfig;
    }

    public function getAclForChoice()
    {
        $choices = [];
        foreach ($this->aclConfig['controllers'] as $controller => $methods) {
            foreach ($methods as $method) {
                $section = $method['section'];
                $group = $method['group'];

                if (isset($choices[$section]) && !array_key_exists($group, $choices[$section])) {
                    $choices[$section][$group] = [];
                }
                $key = $method['label'];
                $value = $controller.'::'.$method['action'];
                $choices[$section][$group][$key] = $value;
            }
        }
        ksort($choices);

        return $choices;
    }

    public function isGranted($user, $controller)
    {
        if ($this->aclEnabled === false) {
            return true;
        }

        if (in_array($controller, $this->getExcludedControllers())) {
            return true;
        }

        if ($user instanceof User === true) {

            /** @var Role $userRole */
            $userRole = $user->getRole();

            if ($userRole instanceof Role) {
                $roleCredentials = $userRole->getCredentials();
                if (count($roleCredentials) > 0) {
                    foreach ($roleCredentials as $credential) {
                        if ($credential == $controller) {
                            return true;
                            break;
                        }
                    }
                }
            }
        }

        return false;
    }

    public function getExcludedControllers()
    {
        $excludedControllers = [];

        foreach ($this->excludedControllers as $controller => $methods) {
            foreach ($methods as $methodName) {
                $entry = $controller.'::'.$methodName;
                $excludedControllers[$entry] = $entry;
            }
        }

        return $excludedControllers;
    }

    public function isEnabled()
    {
        return $this->aclEnabled;
    }
}
