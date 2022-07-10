<?php declare(strict_types=1);

namespace SBSEDV\InputConverter\Converter;

use Psr\Http\Message\ServerRequestInterface;
use SBSEDV\InputConverter\Exception\MalformedContentException;
use SBSEDV\InputConverter\ParsedInput;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface that custom input converters must implement.
 */
interface ConverterInterface
{
    /**
     * Check if the converter supports the request.
     *
     * @param Request|ServerRequestInterface $request The http request.
     */
    public function supports(Request|ServerRequestInterface $request): bool;

    /**
     * Convert a request body to a parsed input object.
     *
     * @param Request|ServerRequestInterface $request The http request.
     *
     * @throws MalformedContentException If the request body is malformed.
     */
    public function convert(Request|ServerRequestInterface $request): ParsedInput;
}
