<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class RequestSubscriber implements EventSubscriberInterface
{
    use TargetPathTrait;

    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        //dd("test");

        $test = $request->getBaseUrl() . $request->getPathInfo();
        if ($test == "https://localhost:8000/profile") {
            dd($request, $event);
        }
        //dd($request);
        if (
            !$event->isMasterRequest()
            || $request->isXmlHttpRequest()
            || preg_match('/^security_/', $request->attributes->get('_route'))
            || preg_match('/^profile_/', $request->attributes->get('_route'))
            || preg_match('/^admin_/', $request->attributes->get('_route'))
            || preg_match('/^_profiler/', $request->attributes->get('_route'))
        ) {
            //$this->saveTargetPath($this->session, 'main', $request->getBaseUrl() . $request->getPathInfo() . "2");
            return;
        }

        $this->saveTargetPath($this->session, 'main', $request->getBaseUrl() . $request->getPathInfo());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest']
        ];
    }
}