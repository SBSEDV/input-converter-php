<?php declare(strict_types=1);

namespace SBSEDV\InputLibrary;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;

class InputLibrary
{
    /** @var Request|ServerRequestInterface|null */
    private $request;

    /** @var TransformerInterface[] */
    private $transformers;

    /**
     * Create an instance of InputLibrary.
     *
     * You can als set / remove the request object via setRequest in case you are having problems
     * with your container builder.
     *
     * @param Request|ServerRequestInterface $request [optional] A http request object.
     *                                                If null is passed, PHPs Globals are used.
     */
    public function __construct(string $request = null)
    {
        $this->request = null;
    }

    /**
     * Set the current http request.
     *
     * @param Request|ServerRequestInterface|null $request The current http request.
     *                                                     If null is passed, PHPs Globals are used.
     */
    public function setRequest($request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get all registered transformers.
     *
     * @return TransformerInterface[]
     */
    public function getTransformers(): array
    {
        return $this->transformers;
    }

    /**
     * Register an array of input transformer.
     *
     * @param TransformerInterface[] $transformers The input transformers.
     */
    public function registerTransformers(array $transformers): self
    {
        foreach ($transformers as $transformer) {
            if ($transformer instanceof TransformerInterface === false) {
                throw new \InvalidArgumentException(vsprintf('Arguments must be of type %s, %s given.', [TransformerInterface::class, gettype($transformer)]));
            }
            $this->transformers[] = $transformer;
        }

        return $this;
    }

    /**
     * Register an input transformer.
     *
     * @param TransformerInterface $transformer The input transformer.
     */
    public function registerTransformer(TransformerInterface $transformer): self
    {
        $this->transformers[] = $transformer;

        return $this;
    }

    /**
     * Start the transformation.
     */
    public function run(): ParsedInput
    {
        foreach ($this->transformers as $transformer) {
            $transformer->setRequest($this->request);

            if ($transformer->supports()) {
                return $transformer->run();
            }
        }

        // if no transformer hit, we return an empty input
        return new ParsedInput();
    }
}
