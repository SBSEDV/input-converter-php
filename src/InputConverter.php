<?php declare(strict_types=1);

namespace SBSEDV\InputConverter;

use SBSEDV\InputConverter\Converter\ConverterInterface;
use SBSEDV\InputConverter\Exception\MalformedContentException;
use SBSEDV\InputConverter\Exception\UnsupportedRequestException;
use SBSEDV\InputConverter\Request\RequestInterface;

class InputConverter
{
    /**
     * @param ConverterInterface[] $converters [optional] A list of input converters to use.
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
    public function getConverters(): array
    {
        return $this->converters;
    }

    /**
     * Add a converter.
     *
     * @param ConverterInterface $converter The input converter to add.
     */
    public function addConverter(ConverterInterface $converter): self
    {
        $this->converters[] = $converter;

        return $this;
    }

    /**
     * Convert the input from the given request.
     *
     * @param RequestInterface $request The http request to convert.
     *
     * @throws MalformedContentException   If the request body is malformed.
     * @throws UnsupportedRequestException If no converter supports the request.
     */
    public function convert(RequestInterface $request): void
    {
        foreach ($this->converters as $converter) {
            if (!$converter->supports($request)) {
                continue;
            }

            try {
                $converter->convert($request);

                return;
            } catch (\Throwable $e) {
                if ($e instanceof MalformedContentException) {
                    throw $e;
                }

                throw new MalformedContentException($e);
            }
        }

        throw new UnsupportedRequestException('No registered input converter supports the request.');
    }
}
