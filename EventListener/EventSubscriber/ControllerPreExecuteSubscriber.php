<?php

namespace AppVerk\UserBundle\EventListener\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use AppVerk\UserBundle\Entity\User;
use AppVerk\UserBundle\Service\Acl\AclProvider;

class ControllerPreExecuteSubscriber implements EventSubscriberInterface
{
    const TEST_REDIRECT_ACTION = 'RedirectController::urlRedirectAction';

    /**
     * @var AclProvider
     */
    private $aclProvider;

    /**
     * @var User
     */
    private $user;
    private $aclEnabled;
    private $environment;

    public function __construct(AclProvider $aclProvider, TokenStorage $tokenStorage, $aclEnabled, $environment)
    {
        $this->user = $tokenStorage->getToken() ? $tokenStorage->getToken()->getUser() : null;
        $this->aclProvider = $aclProvider;
        $this->aclEnabled = $aclEnabled;
        $this->environment = $environment;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(FilterControllerEvent $controllerEvent)
    {
        $pathInfo = $controllerEvent->getRequest()->getPathInfo();

        if ($this->user instanceof User && $this->user->isSuperAdmin() === true) {
            return true;
        }

        if (
            $this->environment == 'dev' &&
            (
                preg_match('/\/_wdt/', $pathInfo) ||
                preg_match('/\/_profiler/', $pathInfo) ||
                preg_match('/\/_error/', $pathInfo) ||
                preg_match('/\/doc/', $pathInfo) ||
                preg_match('/\/translations/', $pathInfo)
            )
        ) {
            return true;
        }

        if (
            $this->aclEnabled !== true ||
            $controllerEvent->isMasterRequest() === false
        ) {
            return true;
        }

        $controllerParts = explode('\\', $controllerEvent->getRequest()->attributes->get('_controller'));
        $controllerName = array_reverse($controllerParts)[0];

        if ($controllerName == self::TEST_REDIRECT_ACTION) {
            return true;
        }

        $status = $this->aclProvider->isGranted($this->user, $controllerName);
        if ($status !== true) {
            throw new AccessDeniedHttpException('Access denied.');
        }

        return true;
    }
}
