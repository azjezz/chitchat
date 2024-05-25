<?php

declare(strict_types=1);

namespace App\Handler\Trait;

use Neu\Component\Http\Exception\HttpException;
use Neu\Component\Http\Message\Form\ParseOptions;
use Neu\Component\Http\Message\Form\ParserInterface;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\StatusCode;

trait SingleFieldFormConvenienceTrait
{
    protected function getValue(ParserInterface $parser, RequestInterface $request, string $field): string
    {
        $options = ParseOptions::create()->withFileCountLimit(0)->withFieldCountLimit(1);

        $form = $parser->parse($request, $options);
        $value = $form->getFirstFieldByName($field)?->getBody()?->getContents();
        if ($value === null) {
            throw new HttpException(StatusCode::BadRequest, message: "$field is required");
        }

        return $value;
    }

}