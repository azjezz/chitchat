<?php

declare(strict_types=1);

namespace App\Handler;

use App\Handler\Trait\SingleFieldFormConvenienceTrait;
use App\Chat\ChatBroadcastingService;
use Neu\Component\Http\Message\Form\ParserInterface;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Message\Response;
use Neu\Component\Http\Message\StatusCode;
use Neu\Component\Http\Router\Route\Route;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;
use Psl\Str;

#[Route(name: 'login', path: '/login', methods: [Method::Post])]
final readonly class LoginHandler implements HandlerInterface
{
    use SingleFieldFormConvenienceTrait;

    public function __construct(
        private ParserInterface $parser,
        private ChatBroadcastingService $broadcasting,
    ) {}

    public function handle(Context $context, RequestInterface $request): ResponseInterface
    {
        if ($request->getSession()->has('username')) {
            return Response\redirect('/chat', statusCode: StatusCode::SeeOther);
        }

        $username = $this->getValue($this->parser, $request, 'username');
        $username = Str\lowercase(Str\trim($username));
        if ('system' === $username) {
            return Response::fromStatusCode(StatusCode::BadRequest);
        }

        $request->getSession()->set('username', $username);

        $this->broadcasting->joined($username);

        return Response\redirect('/chat', statusCode: StatusCode::SeeOther);
    }
}