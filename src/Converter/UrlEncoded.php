<?php declare(strict_types=1);

namespace SBSEDV\Component\InputConverter\Converter;

use Psr\Http\Message\ServerRequestInterface;
use SBSEDV\Component\InputConverter\ParsedInput;
use Symfony\Component\HttpFoundation\Request;

class UrlEncoded extends AbstractConverter
{
    /**
     * @param string[] $contentTypes [optional] The supported http content types.
     * @param string[] $methods      [optional] The supported http methods.
     */
    public function __construct(
        protected array $contentTypes = ['application/x-www-urlencoded'],
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
    public function supports(Request | ServerRequestInterface $request): bool
    {
        return \in_array($request->getMethod(), $this->methods) && \in_array($this->getContentType($request), $this->contentTypes);
    }

    /**
     * {@inheritdoc}
     */
    public function convert(Request | ServerRequestInterface $request): ParsedInput
    {
        \parse_str($this->getContent($request), $array);

        return new ParsedInput($array);
    }
}
