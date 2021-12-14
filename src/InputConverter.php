<?php declare(strict_types=1);

namespace SBSEDV\Component\InputConverter;

use Psr\Http\Message\ServerRequestInterface;
use SBSEDV\Component\InputConverter\Converter\ConverterInterface;
use SBSEDV\Component\InputConverter\Exception\MalformedContentException;
use SBSEDV\Component\InputConverter\Exception\UnsupportedRequestException;
use Symfony\Component\HttpFoundation\Request;

class InputConverter
{
    /**
     * @param ConverterInterface[] $converters [optional] A list of input converters to register.
     */
    public function __construct(
        private array $converters = []
    ) {
    }

    /**
     * Get all registered input converters.
     *
     * @return ConverterInterface[]
     */
    public function getConverts(): array
    {
        return $this->converters;
    }

    /**
     * Register an input converter.
     *
     * @param ConverterInterface $converter The input converter to register.
     */
    public function addConverter(ConverterInterface $converter): self
    {
        $this->converters[] = $converter;

        return $this;
    }

    /**
     * Convert the input from the given request.
     *
     * @param Request|ServerRequestInterface $request The http request to convert.
     *
     * @return ParsedInput The converted input.
     *
     * @throws MalformedContentException   If the request body is malformed.
     * @throws UnsupportedRequestException If no supporting converter was found.
     */
    public function convert(Request | ServerRequestInterface $request = null): ParsedInput
    {
        foreach ($this->converters as $converter) {
            if ($converter->supports($request)) {
                try {
                    return $converter->convert($request);
                } catch (\Throwable $e) {
                    if ($e instanceof MalformedContentException) {
                        throw $e;
                    }

                    throw new MalformedContentException($e);
                }
            }
        }

        throw new UnsupportedRequestException('No supporting input converter for the given request was found.');
    }
}
