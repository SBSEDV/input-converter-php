<?php declare(strict_types=1);

namespace SBSEDV\InputLibrary\Transformer;

use SBSEDV\InputLibrary\ParsedInput;

class UrlEncoded extends AbstractTransformer
{
    protected const CONTENT_TYPE = 'application/x-www-urlencoded';

    /**
     * @param string[] $methods [optional] The supported http methods.
     */
    public function __construct(
        array $methods = ['PUT', 'PATCH', 'DELETE']
    ) {
        // prevent user from overwriting PHPs native parsing
        if (false !== ($key = array_search('POST', $methods, false))) {
            unset($methods[$key]);
        }

        $this->methods = $methods;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(): bool
    {
        return $this->getContentType() === self::CONTENT_TYPE && in_array($this->getMethod(), $this->methods);
    }

    /**
     * {@inheritdoc}
     */
    public function run(): ParsedInput
    {
        parse_str($this->getContent(), $array);

        $parsedInput = new ParsedInput();
        $parsedInput->addValues($array);

        return $parsedInput;
    }
}
