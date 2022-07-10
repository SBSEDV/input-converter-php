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
     */
    protected function getContentType(Request|ServerRequestInterface $request): string
    {
        $headers = [];

        if ($request instanceof Request) {
            $headers = $request->headers->all('Content-Type');
        } else {
            $headers = $request->getHeader('Content-Type');
        }

        $contentType = reset($headers);

        return \is_string($contentType) ? $contentType : '';
    }

    /**
     * Get the content (body) of the request as string.
     *
     * @param Request|ServerRequestInterface $request The request to get the content from.
     */
    protected function getContent(Request|ServerRequestInterface $request): string
    {
        if ($request instanceof Request) {
            return $request->getContent();
        }

        return (string) $request->getBody();
    }
}
