<?php declare(strict_types=1);

namespace SBSEDV\InputConverter\Converter;

use Psr\Http\Message\ServerRequestInterface;
use SBSEDV\InputConverter\ParsedInput;
use Symfony\Component\HttpFoundation\Request;

class UrlEncodedConverter extends AbstractConverter
{
    protected const ALLOWED_CONTENT_TYPE = 'application/x-www-urlencoded';

    /**
     * @param string[] $methods [optional] The supported http methods.
     */
    public function __construct(
        protected array $methods = ['PUT', 'PATCH', 'DELETE']
    ) {
        // prevent user from overwriting PHPs native parsing
        if (false !== ($key = array_search('POST', $this->methods, false))) {
            unset($this->methods[$key]);
        }
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
            if (\str_starts_with($contentType, self::ALLOWED_CONTENT_TYPE)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(Request|ServerRequestInterface $request): ParsedInput
    {
        \parse_str($this->getContent($request), $array);

        return new ParsedInput(static::class, $array);
    }
}
