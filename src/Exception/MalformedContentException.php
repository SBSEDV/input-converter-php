<?php declare(strict_types=1);

namespace SBSEDV\InputConverter\Exception;

class MalformedContentException extends \Exception implements ExceptionInterface
{
    /**
     * {@inheritdoc}
     */
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct('The request content structure does not match the expected format.', previous: $previous);
    }
}
