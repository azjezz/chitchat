<?php

declare(strict_types=1);

namespace App\Handler;

use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Message\Response;
use Neu\Component\Http\Message\StatusCode;
use Neu\Component\Http\Router\Route;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;
use Twig\Environment;

#[Route(name: 'chat', pattern: '/chat', methods: [Method::Get])]
final readonly class ChatHandler implements HandlerInterface
{
    public function __construct(
        private Environment $twig
    ) {}

    public function handle(Context $context, RequestInterface $request): ResponseInterface
    {
        $username = $request->getQueryParameter('username');
        if (null === $username) {
            return Response\redirect('/', statusCode: StatusCode::SeeOther);
        }

        $username = $username[0];

        $content = $this->twig->render('chat.html.twig', [
            'username' => $username,
        ]);

        return Response\html($content);
    }
}