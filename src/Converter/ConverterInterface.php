<?php declare(strict_types=1);

namespace SBSEDV\InputConverter\Converter;

use SBSEDV\InputConverter\Exception\MalformedContentException;
use SBSEDV\InputConverter\Request\RequestInterface;
use Symfony\Component\HttpFoundation\Request;

interface ConverterInterface
{
    /**
     * Check if the converter supports the request.
     *
     * @param RequestInterface $request The http request.
     */
    public function supports(RequestInterface $request): bool;

    /**
     * Convert a request body to a parsed input object.
     *
     * @param RequestInterface $request The http request.
     *
     * @throws MalformedContentException If the request body is malformed.
     */
    public function convert(RequestInterface $request): void;
}
