<?php

declare(strict_types=1);

namespace App\Handler;

use App\Handler\Trait\SingleFieldFormConvenienceTrait;
use Neu\Component\Broadcast\HubInterface;
use Neu\Component\Http\Exception\HttpException;
use Neu\Component\Http\Message\Form\ParseOptions;
use Neu\Component\Http\Message\Form\ParserInterface;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\Response;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Message\StatusCode;
use Neu\Component\Http\Router\Route\Route;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;
use Psl\Html;

#[Route(name: 'message', path: '/message', methods: [Method::Post])]
final readonly class MessageHandler implements HandlerInterface
{
    use SingleFieldFormConvenienceTrait;

    public function __construct(
        private ParserInterface $parser,
        private HubInterface $hub,
    ) {}

    public function handle(Context $context, RequestInterface $request): ResponseInterface
    {
        $form = $this->parser->parse($request, ParseOptions::create()->withFileCountLimit(0)->withFieldCountLimit(2));

        $username = $form->getFirstFieldByName('username')?->getBody()?->getContents();
        $message = $form->getFirstFieldByName('message')?->getBody()?->getContents();

        if (null === $username) {
            throw new HttpException(StatusCode::BadRequest, message: 'Missing username');
        }

        if (null === $message) {
            throw new HttpException(StatusCode::BadRequest, message: 'Missing message');
        }

        if ('' !== $username && '' !== $message) {
            $this->hub->getChannel('chat')->broadcast([
                'username' => Html\encode_special_characters($username),
                'message' => Html\encode_special_characters($message),
            ]);
        }

        return Response::fromStatusCode(StatusCode::NoContent);
    }
}
