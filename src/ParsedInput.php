<?php declare(strict_types=1);

namespace SBSEDV\InputConverter;

use Symfony\Component\HttpFoundation\Request;

class ParsedInput
{
    public function __construct(
        private array $values = [],
        private array $files = []
    ) {
    }

    /**
     * Add parsed input values.
     *
     * @param array $values The parsed input values.
     */
    public function addValues(array $values): self
    {
        foreach ($values as $key => $value) {
            $this->values[$key] = $value;
        }

        return $this;
    }

    /**
     * Get the parsed input values.
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Add parsed input files.
     *
     * @param array $files The parsed input files.
     */
    public function addFiles(array $files): self
    {
        foreach ($files as $key => $value) {
            $this->files[$key] = $value;
        }

        return $this;
    }

    /**
     * Get the parsed input files.
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Add the parsed input to an Http-Foundation request object.
     *
     * @param Request $request The http-foundation request object.
     */
    public function applyOnHttpFoundationRequest(Request &$request): void
    {
        $request->request->add($this->values);
        $request->files->add($this->files);
    }

    /**
     * Apply the parsed input to the PHP super globals $_POST and $_FILES.
     */
    public function applyOnGlobals(): void
    {
        $_POST = $this->values;
        $_FILES = $this->files;
    }
}
