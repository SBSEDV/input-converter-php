<?php declare(strict_types=1);

namespace SBSEDV\InputConverter\Request;

interface RequestInterface
{
    /**
     * The http-method of the request.
     */
    public function getMethod(): string;

    /**
     * The http body of the request.
     */
    public function getContent(): string;

    /**
     * The "Content-Type" header values.
     *
     * @return string[]
     */
    public function getContentTypes(): array;

    /**
     * Populate the parsed input data on the underlying request object.
     *
     * @param array $params The parsed input parameters.
     * @param array $files  The parsed file uploads.
     */
    public function populate(array $params = [], array $files = []): void;
}
