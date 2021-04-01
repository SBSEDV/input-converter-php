<?php declare(strict_types=1);

namespace SBSEDV\InputLibrary\Transformer;

use Psr\Http\Message\ServerRequestInterface;
use SBSEDV\InputLibrary\TransformerInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractTransformer implements TransformerInterface
{
    /** @var string[] */
    protected $methods;

    /** @var Request|ServerRequestInterface|null */
    protected $request;

    /**
     * {@inheritdoc}
     */
    public function setRequest($request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * The method of the current request.
     */
    public function getMethod(): string
    {
        if (null === $this->request) {
            return $_SERVER['REQUEST_METHOD'];
        }

        // PSR-7 and HTTP-Foundation have the same method
        return $this->request->getMethod();
    }

    /**
     * The content type of the current request.
     */
    public function getContentType(): string
    {
        if (null === $this->request) {
            return $_SERVER['HTTP_CONTENT_TYPE'];
        }

        if ($this->isHttpFoundation()) {
            return $this->request->headers->get('Content-Type', '');
        }

        return $this->request->getHeader('Content-Type')[0];
    }

    /**
     * The content of the current request as string.
     */
    public function getContent(): string
    {
        if (null === $this->request) {
            return file_get_contents('php://input');
        }

        if ($this->isHttpFoundation()) {
            return $this->request->getContent();
        }

        return $this->request->getBody()->__toString();
    }

    protected function isHttpFoundation(): bool
    {
        return $this->request instanceof Request;
    }
}
