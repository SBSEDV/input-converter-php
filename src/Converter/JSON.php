<?php declare(strict_types=1);

namespace SBSEDV\InputConverter\Converter;

use Psr\Http\Message\ServerRequestInterface;
use SBSEDV\InputConverter\ParsedInput;
use Symfony\Component\HttpFoundation\Request;

class JSON extends AbstractConverter
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
        return \in_array($request->getMethod(), $this->methods) && \in_array($this->getContentType($request), $this->contentTypes);
    }

    /**
     * {@inheritdoc}
     */
    public function convert(Request|ServerRequestInterface $request): ParsedInput
    {
        $array = \json_decode($this->getContent($request), true, flags: \JSON_THROW_ON_ERROR) ?? [];

        return new ParsedInput(static::class, $array);
    }
}
