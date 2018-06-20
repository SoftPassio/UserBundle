<?php

namespace AppVerk\UserBundle\Service\Acl;

use AppVerk\Components\Model\UserInterface;
use Symfony\Component\Yaml\Yaml;

class AclProvider
{
    private $aclConfig = [];

    private $aclEnabled;

    private $rootDir;

    private $excludedControllers = [];
    private $redirectPath;

    public function __construct($rootDir, $aclEnabled, $redirectPath)
    {
        $this->rootDir = $rootDir;
        $this->aclEnabled = $aclEnabled;
        $this->redirectPath = $redirectPath;
        $this->buildAclConfig();
    }

    private function buildAclConfig()
    {
        $bundlesDirectory = $this->rootDir.'/../src/Bundle/';
        if(!file_exists($bundlesDirectory)){
            $bundlesDirectory = $this->rootDir.'/../src/';
        }
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
        if (empty($this->aclConfig) && $this->aclEnabled) {
            throw new \Exception('acl.yml file need to be configured');
        }
    }

    public function getAclConfig()
    {
        return $this->aclConfig;
    }

    public function getUnauthorizedRedirect()
    {
        return $this->redirectPath;
    }

    public function isGranted(UserInterface $user, $controller)
    {
        if ($this->aclEnabled === false) {
            return true;
        }

        $controllerActionParams = explode("::", $controller);

        if (in_array($controllerActionParams[0], $this->getExcludedControllers())) {
            return true;
        }

        if ($user instanceof UserInterface) {
            $userRoles = $user->getRoles();

            foreach ($this->aclConfig as $controllersArray) {
                foreach ($controllersArray as $controller => $methodsArray) {
                    if(empty($methodsArray)){
                        continue;
                    }
                    foreach ($methodsArray as $method) {
                        if(empty($method['allow']) && $controller === $controllerActionParams[0] && $method['action'] === $controllerActionParams[1]){
                            return true;
                        }
                        if ($controller !== $controllerActionParams[0] || $method['action'] !== $controllerActionParams[1]) {
                            continue;
                        }
                        foreach ($userRoles as $role) {
                            if (in_array($role, $method['allow'])) {
                                return true;
                            }
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
            if ($methods === null) {
                $excludedControllers[$controller] = $controller;
                continue;
            }
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
