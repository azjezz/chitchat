<?php

declare(strict_types=1);

namespace App\Handler;

use App\Chat\ChatSubscriptionService;
use Neu\Component\Http\Exception\HttpException;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Message\StatusCode;
use Neu\Component\Http\Router\Route\Route;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;
use Neu\Component\Http\ServerSentEvent\Event;
use Neu\Component\Http\ServerSentEvent\EventStream;
use Psl\Json;
use Revolt\EventLoop;

#[Route(name: 'subscribe', path: '/subscribe', methods: [Method::Get])]
final readonly class SubscribeHandler implements HandlerInterface
{
    public function __construct(
        private ChatSubscriptionService $subscription,
    ) {}

    public function handle(Context $context, RequestInterface $request): ResponseInterface
    {
        if (!$request->getSession()->has('username')) {
            throw new HttpException(StatusCode::Unauthorized);
        }

        $stream = EventStream::forContext($context);

        EventLoop::queue(function() use($stream): void {
            $id = $this->subscription->subscribe();

            foreach ($this->subscription->getPipeline($id) as $data) {
                if ($stream->isClosed()) {
                    $this->subscription->unsubscribe($id);

                    return;
                }

                $stream->send(new Event(
                    type: 'message',
                    data: Json\encode($data)
                ));
            }
        });

        return $stream->getResponse();
    }
}
