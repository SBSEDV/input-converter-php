<?php declare(strict_types=1);

namespace SBSEDV\Component\InputConverter;

use Psr\Http\Message\ServerRequestInterface;
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
    public function supports(Request | ServerRequestInterface $request): bool;

    /**
     * the request body and returns the found user input.
     *
     * @param Request|ServerRequestInterface $request The http request.
     */
    public function convert(Request | ServerRequestInterface $request): ParsedInput;
}
