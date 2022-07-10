<?php declare(strict_types=1);

namespace SBSEDV\InputConverter\Converter;

use Psr\Http\Message\ServerRequestInterface;
use SBSEDV\InputConverter\Exception\MalformedContentException;
use SBSEDV\InputConverter\ParsedInput;
use Symfony\Component\HttpFoundation\Request;

class JsonConverter extends AbstractConverter
{
    /**
     * @param string[] $contentTypes [optional] The supported http content types.
     * @param string[] $methods      [optional] The supported http methods.
     */
    public function __construct(
        protected array $contentTypes = ['application/json'],
        protected array $methods = ['POST', 'PUT', 'PATCH', 'DELETE']
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request|ServerRequestInterface $request): bool
    {
        if (!\in_array($request->getMethod(), $this->methods, true)) {
            return false;
        }

        foreach ($this->getContentTypes($request) as $contentType) {
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
    public function convert(Request|ServerRequestInterface $request): ParsedInput
    {
        try {
            $array = \json_decode($this->getContent($request), true, flags: \JSON_THROW_ON_ERROR) ?? [];
        } catch (\JsonException $e) {
            throw new MalformedContentException($e);
        }

        return new ParsedInput(static::class, $array); // @phpstan-ignore-line
    }
}
