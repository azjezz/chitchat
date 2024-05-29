<?php

declare(strict_types=1);

namespace App\Handler;

use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Message\Response;
use Neu\Component\Http\Router\Route;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;
use Twig\Environment;

#[Route(name: 'index', pattern: '/', methods: [Method::Get])]
final readonly class IndexHandler implements HandlerInterface
{
    public function __construct(
        private Environment $twig
    ) {}

    public function handle(Context $context, RequestInterface $request): ResponseInterface
    {
        return Response\html(
            $this->twig->render('index.html.twig')
        );
    }
}