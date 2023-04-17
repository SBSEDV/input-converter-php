<?php declare(strict_types=1);

namespace SBSEDV\InputConverter\Converter;

use SBSEDV\InputConverter\Exception\MalformedContentException;
use SBSEDV\InputConverter\Request\RequestInterface;

class JsonConverter implements ConverterInterface
{
    /**
     * @param string[] $contentTypes [optional] The supported http content types.
     * @param string[] $methods      [optional] The supported http methods.
     */
    public function __construct(
        private array $contentTypes = ['application/json'],
        private array $methods = ['POST', 'PUT', 'PATCH', 'DELETE']
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function supports(RequestInterface $request): bool
    {
        if (!\in_array($request->getMethod(), $this->methods, true)) {
            return false;
        }

        foreach ($request->getContentTypes() as $contentType) {
            foreach ($this->contentTypes as $allowedContentType) {
                // use str_starts_with because CT is often send as "application/json; charset=utf8"
                if (\str_starts_with($contentType, $allowedContentType)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(RequestInterface $request): void
    {
        try {
            $array = \json_decode($request->getContent(), true, flags: \JSON_THROW_ON_ERROR) ?? [];
        } catch (\JsonException $e) {
            throw new MalformedContentException($e);
        }

        $request->populate((array) $array);
    }
}
