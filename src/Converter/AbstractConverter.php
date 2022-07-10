<?php declare(strict_types=1);

namespace SBSEDV\InputConverter\Converter;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractConverter implements ConverterInterface
{
    /**
     * Get the content type header of the given request.
     *
     * @param Request|ServerRequestInterface $request The request to get the header from.
     *
     * @return string[]
     */
    protected function getContentTypes(Request|ServerRequestInterface $request): array
    {
        if ($request instanceof Request) {
            return $request->headers->all('content-type'); // @phpstan-ignore-line
        }

        return $request->getHeader('content-type');
    }

    /**
     * Get the content (body) of the request as string.
     *
     * @param Request|ServerRequestInterface $request The request to get the content from.
     */
    protected function getContent(Request|ServerRequestInterface $request): string
    {
        if ($request instanceof Request) {
            // @phpstan-ignore-next-line
            return $request->getContent();
        }

        return (string) $request->getBody();
    }
}
