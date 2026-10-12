<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;

#[AsEventListener(event: 'kernel.request', priority: 256)]
final class StripTrailingQuoteListener
{
    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = rawurldecode($request->getPathInfo());
        $clean = rtrim($path, "\"' \\");

        if ($clean === $path) {
            return;
        }

        if ($clean === '') {
            $clean = '/';
        }

        $qs = $request->getQueryString();
        $event->setResponse(new RedirectResponse(
            $clean . ($qs ? '?' . $qs : ''),
            301
        ));
    }
}