<?php declare(strict_types=1);

namespace SBSEDV\InputConverter\Converter;

use SBSEDV\InputConverter\Request\RequestInterface;

class UrlEncodedConverter implements ConverterInterface
{
    protected const ALLOWED_CONTENT_TYPE = 'application/x-www-form-urlencoded';

    /**
     * @param string[] $methods [optional] The supported http methods.
     */
    public function __construct(
        private array $methods = ['PUT', 'PATCH', 'DELETE']
    ) {
        // prevent user from overwriting PHPs native parsing
        if (false !== ($key = \array_search('POST', $this->methods, false))) {
            unset($this->methods[$key]);
        }
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
            if (\str_starts_with($contentType, self::ALLOWED_CONTENT_TYPE)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(RequestInterface $request): void
    {
        \parse_str($request->getContent(), $array);

        $request->populate($array);
    }
}
