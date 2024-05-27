<?php

declare(strict_types=1);

namespace App\Listener;

use Neu\Component\Broadcast\HubInterface;
use Neu\Component\EventDispatcher\Attribute\Listener;
use Neu\Component\EventDispatcher\Listener\ListenerInterface;
use Neu\Component\Http\Server\Event\ServerStartedEvent;
use Neu\Component\Http\Server\Event\ServerStoppingEvent;

/**
 * A listener that listens for server events.
 *
 * @implements ListenerInterface<ServerStartedEvent>
 */
#[Listener(events: [ServerStoppingEvent::class])]
final readonly class SystemListener implements ListenerInterface
{
    public function __construct(
        private HubInterface $hub,
    ) {}

    public function process(object $event): object
    {
        if ($event instanceof ServerStoppingEvent) {
            $this->hub->close();
        }

        return $event;
    }
}