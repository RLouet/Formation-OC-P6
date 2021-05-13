<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LogoutListener
{
    use TargetPathTrait;

    public function onSymfonyComponentSecurityHttpEventLogoutEvent(LogoutEvent $event)
    {
        $redirectUrl = $event->getRequest()->headers->get('referer');
        if ($redirectUrl) {
            $event->setResponse(new RedirectResponse($event->getRequest()->headers->get('referer')));
        }
    }


}