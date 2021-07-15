<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class RequestSubscriber implements EventSubscriberInterface
{
    use TargetPathTrait;

    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (
            !$event->isMainRequest()
            || $request->isXmlHttpRequest()
            || preg_match('/^profile_/', $request->attributes->get('_route'))
            || preg_match('/^admin_/', $request->attributes->get('_route'))
            || preg_match('/^_profiler/', $request->attributes->get('_route'))
            || preg_match('/^front_tricks-edit/', $request->attributes->get('_route'))
            || preg_match('/^front_tricks-add/', $request->attributes->get('_route'))
            || preg_match('/^security_/', $request->attributes->get('_route'))
        ) {
            return;
        }

        $this->requestStack->getSession()->set('origin_path', $request->getPathInfo());
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest']
        ];
    }
}
