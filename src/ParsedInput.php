<?php declare(strict_types=1);

namespace SBSEDV\InputConverter;

class ParsedInput
{
    public function __construct(
        private string $converterName,
        private array $values = [],
        private array $files = []
    ) {
    }

    /**
     * The converter that parsed the request.
     */
    public function getConverterName(): string
    {
        return $this->converterName;
    }

    /**
     * Get the parsed input values.
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Get the parsed input files.
     */
    public function getFiles(): array
    {
        return $this->files;
    }
}
