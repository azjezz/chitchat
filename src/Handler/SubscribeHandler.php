<?php

declare(strict_types=1);

namespace App\Handler;

use Amp\ByteStream\ReadableIterableStream;
use App\Chat\ChatSubscriptionService;
use Neu\Component\Http\Exception\HttpException;
use Neu\Component\Http\Message\Body;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Message\Response;
use Neu\Component\Http\Message\StatusCode;
use Neu\Component\Http\Router\Route\Route;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;
use Psl\Json;

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

        $id = $this->subscription->subscribe();

        $context->getClient()->onClose(function() use ($id): void {
            $this->subscription->unsubscribe($id);
        });

        $stream = new ReadableIterableStream(
            $this->subscription->getPipeline($id)->map(static function($data) {
                return 'event: message' . "\n" . 'data: ' . Json\encode($data) . "\n\n";
            })->getIterator()
        );

        return Response::fromStatusCode(StatusCode::OK)
            ->withHeader('Content-Type', 'text/event-stream')
            ->withHeader('Cache-Control', 'no-cache')
            ->withHeader('Connection', 'keep-alive')
            ->withBody(Body::fromReadableStream(stream: $stream))
        ;
    }
}
