<?php

namespace AppVerk\UserBundle\EventListener\EventSubscriber;

use AppVerk\Components\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use AppVerk\UserBundle\Service\Acl\AclProvider;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ControllerPreExecuteSubscriber implements EventSubscriberInterface
{
    const TEST_REDIRECT_ACTION = 'RedirectController::urlRedirectAction';

    /**
     * @var AclProvider
     */
    private $aclProvider;

    /**
     * @var UserInterface
     */
    private $user;
    private $aclEnabled;
    private $environment;
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(AclProvider $aclProvider, TokenStorageInterface $tokenStorage, RouterInterface $router,
        $aclEnabled, $environment)
    {
        $this->user = $tokenStorage->getToken() ? $tokenStorage->getToken()->getUser() : null;
        $this->aclProvider = $aclProvider;
        $this->aclEnabled = $aclEnabled;
        $this->environment = $environment;
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
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

        if (!$this->user instanceof UserInterface) {
            return true;
        }

        if (
            $this->environment == 'dev' &&
            (
                preg_match('/\/_wdt/', $pathInfo) ||
                preg_match('/\/_profiler/', $pathInfo) ||
                preg_match('/\/_error/', $pathInfo) ||
                preg_match('/\/doc/', $pathInfo)
            )
        ) {
            return true;
        }

        if (preg_match('/\/translations/', $pathInfo)) {
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
            $route = $this->aclProvider->getUnauthorizedRedirect();
            $redirectUrl = $this->router->generate($route);

            $controllerEvent->setController(function() use ($redirectUrl) {
                return new RedirectResponse($redirectUrl);
            });
            return false;
        }
        return true;
    }
}
