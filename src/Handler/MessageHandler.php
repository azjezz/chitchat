<?php

declare(strict_types=1);

namespace App\Handler;

use App\Handler\Trait\SingleFieldFormConvenienceTrait;
use App\Chat\ChatBroadcastingService;
use Neu\Component\Http\Exception\HttpException;
use Neu\Component\Http\Message\Form\ParserInterface;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\Response;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Message\StatusCode;
use Neu\Component\Http\Router\Route\Route;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;

#[Route(name: 'message', path: '/message', methods: [Method::Post])]
final readonly class MessageHandler implements HandlerInterface
{
    use SingleFieldFormConvenienceTrait;

    public function __construct(
        private ParserInterface         $parser,
        private ChatBroadcastingService $broadcasting,
    ) {}

    public function handle(Context $context, RequestInterface $request): ResponseInterface
    {
        if (!$request->getSession()->has('username')) {
            throw new HttpException(StatusCode::Unauthorized);
        }

        $username = $request->getSession()->get('username');
        $message = $this->getValue($this->parser, $request, 'message');

        if ('' !== $message) {
            $this->broadcasting->broadcast($username, $message);
        }

        return Response::fromStatusCode(StatusCode::NoContent);
    }
}
