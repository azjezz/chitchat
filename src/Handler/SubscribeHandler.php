<?php

declare(strict_types=1);

namespace App\Handler;

use Neu\Component\Broadcast\HubInterface;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Router\Route;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;
use Neu\Component\Http\ServerSentEvent\Event;
use Neu\Component\Http\ServerSentEvent\EventStream;
use Psl\Async;
use Psl\Json;

#[Route(name: 'subscribe', pattern: '/subscribe', methods: [Method::Get])]
final readonly class SubscribeHandler implements HandlerInterface
{
    public function __construct(
        private HubInterface $hub,
    ) {}

    public function handle(Context $context, RequestInterface $request): ResponseInterface
    {
        $stream = EventStream::forContext($context);

        $subscription = $this->hub->getChannel('chat')->subscribe();

        Async\run(function () use($subscription, $stream): void {
            while ($message = $subscription->receive()) {
                if ($stream->isClosed()) {
                    // cancel the subscription if the client has disconnected.
                    $subscription->cancel();

                    return;
                }

                // Send the message to the client.
                $stream->send(new Event(
                    type: 'message',
                    data: Json\encode($message->getPayload())
                ));
            }

            // Close the stream when the subscription ends ( hub/channel is closed ).
            $stream->close();
        })->ignore();

        return $stream->getResponse();
    }
}
