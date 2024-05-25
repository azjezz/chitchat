<?php

declare(strict_types=1);

namespace App\Handler;

use App\Handler\Trait\SingleFieldFormConvenienceTrait;
use Neu\Component\Http\Message\Form\ParserInterface;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Message\Response;
use Neu\Component\Http\Message\StatusCode;
use Neu\Component\Http\Router\Route\Route;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;

#[Route(name: 'logout', path: '/logout', methods: [Method::Get])]
final readonly class LogoutHandler implements HandlerInterface
{
    use SingleFieldFormConvenienceTrait;

    public function __construct(
        private ParserInterface $parser,
    ) {}

    public function handle(Context $context, RequestInterface $request): ResponseInterface
    {
        $request->getSession()->flush();

        return Response\redirect('/', statusCode: StatusCode::SeeOther);
    }
}