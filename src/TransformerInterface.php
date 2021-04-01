<?php declare(strict_types=1);

namespace SBSEDV\InputLibrary;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface that custom transformers must implement.
 */
interface TransformerInterface
{
    /**
     * Set the http request.
     *
     * @param Request|ServerRequestInterface|null $request The http request.
     */
    public function setRequest($request): self;

    /**
     * Check if your transformer supports the current request.
     */
    public function supports(): bool;

    /**
     * Parses the request body and returns the found user input.
     */
    public function run(): ParsedInput;
}
