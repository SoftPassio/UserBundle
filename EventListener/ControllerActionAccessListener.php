<?php

namespace AppVerk\UserBundle\EventListener;

use AppVerk\UserBundle\Entity\RoleableInterface;
use AppVerk\UserBundle\Security\AccessResolverInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ControllerActionAccessListener
{
    private $aclEnabled;

    private $accessDeniedPath;

    /**
     * @var bool
     */
    private $accessDeniedShowFlashMessage = false;

    /**
     * @var AccessResolverInterface
     */
    private $accessResolver;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(
        AccessResolverInterface $accessResolver,
        TokenStorageInterface $tokenStorage,
        RouterInterface $router,
        SessionInterface $session
    ) {
        $this->accessResolver = $accessResolver;
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
        $this->session = $session;
    }

    public function setSettings($aclEnabled, $accessDeniedPath, bool $accessDeniedShowFlashMessage = false)
    {
        $this->aclEnabled = $aclEnabled;
        $this->accessDeniedPath = $accessDeniedPath;
        $this->accessDeniedShowFlashMessage = $accessDeniedShowFlashMessage;
    }

    /**
     * @param FilterControllerEvent $event
     * @return RedirectResponse|void
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!$this->aclEnabled) {
            return;
        }

        $controller = $event->getController();

        if (!is_array($controller) || !$event->isMasterRequest()) {
            return;
        }

        $user = $this->getUser();

        if (!$user instanceof RoleableInterface) {
            return;
        }

        if (!$this->accessResolver->resolve($user, $event->getRequest()->attributes->get('_controller'))) {
            $this->buildResponse($event);
        }

        return;
    }

    private function buildResponse(FilterControllerEvent $event)
    {
        if (!$this->accessDeniedPath) {
            throw new AccessDeniedHttpException("Access denied", new \Exception());
        }

        if (!$this->router->getRouteCollection()->get($this->accessDeniedPath)) {
            throw new \Exception('access_denied_path parameter under app_verk_app_user.acl is not valid action name');
        }

        if ($this->accessDeniedShowFlashMessage) {
            $this->session->getFlashBag()->add('danger', 'security.route.access_denied');
        }

        $event->setController(
            function () {
                return new RedirectResponse($this->router->generate($this->accessDeniedPath));
            }
        );
    }


    private function getUser()
    {
        $tokenStorage = $this->tokenStorage->getToken();

        return ($tokenStorage) ? $tokenStorage->getUser() : null;
    }
}
