<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event subscriber to prevent browser caching of dynamic pages.
 * This ensures authenticated pages are not accessible via browser "Back" button
 * after logout.
 */
class NoCacheSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        // Only apply to main request (not sub-requests)
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();

        // Set headers to prevent browser caching
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate', true);
        $response->headers->set('Pragma', 'no-cache', true);
        $response->headers->set('Expires', '0', true);
    }
}