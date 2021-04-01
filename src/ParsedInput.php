<?php declare(strict_types=1);

namespace SBSEDV\InputLibrary;

use Symfony\Component\HttpFoundation\Request;

class ParsedInput
{
    /** @var array */
    private $files = [];

    /** @var array */
    private $values = [];

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
     * Add the parsed input the an Http-Foundation Request object.
     *
     * @param Request $request The http-foundation request object.
     */
    public function toHttpFoundation(Request &$request): void
    {
        $request->request->add($this->getValues());
        $request->files->add($this->getFiles());
    }

    /**
     * Add the parsed input to the PHP super globals $_POST and $_FILES.
     */
    public function toGlobals(): void
    {
        $_POST = $this->getValues();
        $_FILES = $this->getFiles();
    }
}
