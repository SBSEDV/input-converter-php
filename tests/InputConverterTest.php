<?php declare(strict_types=1);

namespace SBSEDV\InputConverter\Tests;

use PHPUnit\Framework\TestCase;
use SBSEDV\InputConverter\Converter\JsonConverter;
use SBSEDV\InputConverter\Converter\UrlEncodedConverter;
use SBSEDV\InputConverter\Exception\UnsupportedRequestException;
use SBSEDV\InputConverter\InputConverter;
use SBSEDV\InputConverter\Request\HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Request;

class InputConverterTest extends TestCase
{
    public function testAddConverter(): void
    {
        $inputConverter = new InputConverter();
        $inputConverter->addConverter(new JsonConverter());

        $converters = $inputConverter->getConverters();

        $this->assertIsArray($converters);
        $this->exactly(\count($converters), 1);
    }

    public function testNoSupportingConverter(): void
    {
        $request = Request::create('/');
        $request->headers->set('content-type', 'application/does-not-exist');

        $inputConverter = new InputConverter([
            new UrlEncodedConverter(),
        ]);

        $this->expectException(UnsupportedRequestException::class);
        $inputConverter->convert(new HttpFoundationRequest($request));
    }
}
