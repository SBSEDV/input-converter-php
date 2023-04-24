<?php declare(strict_types=1);

namespace SBSEDV\InputConverter\Request;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;

class Psr7Request implements RequestInterface
{
    public function __construct(
        private ServerRequestInterface $request,
        private ?UploadedFileFactoryInterface $uploadedFileFactory = null,
        private ?StreamFactoryInterface $streamFactory = null
    ) {
    }

    public function getRequest(): ServerRequestInterface
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
        return (string) $this->request->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function getContentTypes(): array
    {
        return $this->request->getHeader('content-type');
    }

    /**
     * {@inheritdoc}
     */
    public function populate(array $params = [], array $files = []): void
    {
        $uploadedFiles = [];

        if (\count($files) > 0) {
            if (null === $this->uploadedFileFactory || null === $this->streamFactory) {
                throw new \LogicException('You must pass an UploadedFileFactory and a StreamInterface if file uploads are allowed.');
            }

            foreach ($files as $file) {
                $uploadedFiles[] = $this->uploadedFileFactory->createUploadedFile(
                    $this->streamFactory->createStreamFromFile($file['tmp_name']),
                    $file['size'] ?? null,
                    $file['error'] ?? \UPLOAD_ERR_OK,
                    $file['name'] ?? null,
                    $file['type'] ?? null
                );
            }
        }

        $this->request = $this->request
            ->withParsedBody($params)
            ->withUploadedFiles($uploadedFiles)
        ;
    }
}
