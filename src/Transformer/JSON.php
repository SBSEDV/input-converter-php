<?php declare(strict_types=1);

namespace SBSEDV\InputLibrary\Transformer;

use SBSEDV\InputLibrary\ParsedInput;

class JSON extends AbstractTransformer
{
    /** @var string[] */
    protected $contentTypes;

    /**
     * @param string[] $contentTypes [optional] The supported http content types.
     * @param string[] $methods      [optional] The supported http methods.
     */
    public function __construct(
        array $contentTypes = ['application/json'],
        array $methods = ['POST', 'PUT', 'PATCH', 'DELETE']
    ) {
        $this->contentTypes = $contentTypes;
        $this->methods = $methods;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(): bool
    {
        return in_array($this->getContentType(), $this->contentTypes) && in_array($this->getMethod(), $this->methods);
    }

    /**
     * {@inheritdoc}
     */
    public function run(): ParsedInput
    {
        // on failure json_decode returns null
        $array = json_decode($this->getContent(), true) ?? [];

        $parsedInput = new ParsedInput();
        $parsedInput->addValues($array);

        return $parsedInput;
    }
}
