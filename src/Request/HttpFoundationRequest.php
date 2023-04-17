<?php declare(strict_types=1);

namespace SBSEDV\InputConverter\Request;

use Symfony\Component\HttpFoundation\Request;

class HttpFoundationRequest implements RequestInterface
{
    public function __construct(
        private Request $request
    ) {
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod(): string
    {
        return $this->request->getMethod();
    }

    /**
     * {@inheritdoc}
     */
    public function getContent(): string
    {
        return $this->request->getContent();
    }

    /**
     * {@inheritdoc}
     */
    public function getContentTypes(): array
    {
        return $this->request->headers->all('content-type');
    }

    /**
     * {@inheritdoc}
     */
    public function populate(array $params = [], array $files = []): void
    {
        foreach ($params as $key => $value) {
            $this->request->request->set($key, $value);
        }

        foreach ($files as $key => $value) {
            $this->request->files->set($key, $value);
        }
    }
}
