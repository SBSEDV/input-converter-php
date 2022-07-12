<?php declare(strict_types=1);

namespace SBSEDV\InputConverter\Tests;

use PHPUnit\Framework\TestCase;
use SBSEDV\InputConverter\Converter\FormDataConverter;
use SBSEDV\InputConverter\Converter\JsonConverter;
use SBSEDV\InputConverter\Converter\UrlEncodedConverter;
use SBSEDV\InputConverter\Exception\UnsupportedRequestException;
use SBSEDV\InputConverter\InputConverter;
use SBSEDV\InputConverter\ParsedInput;
use Symfony\Component\HttpFoundation\Request;

class InputConverterTest extends TestCase
{
    public function testAddConverter(): void
    {
        $inputConverter = $this->createInputConverter();
        $inputConverter->addConverter(new JsonConverter());

        $converters = $inputConverter->getConverters();

        $this->assertIsArray($converters);
        $this->exactly(\count($converters), 2);
    }

    public function testNoSupportingConverter(): void
    {
        $request = Request::create('/');
        $request->headers->set('content-type', 'application/does-not-exist');

        $inputConverter = $this->createInputConverter();

        $this->expectException(UnsupportedRequestException::class);
        $inputConverter->convert($request);
    }

    public function testUrlEncodedConverter(): void
    {
        $content = $this->createMockData();
        $content = \http_build_query($content);

        $request = Request::create('/', 'PUT', content: $content);
        $request->headers->set('content-type', 'application/x-www-form-urlencoded');

        $inputConverter = $this->createInputConverter();

        $parsedInput = $inputConverter->convert($request);

        $this->assertInstanceOf(ParsedInput::class, $parsedInput);
        $this->assertEquals(UrlEncodedConverter::class, $parsedInput->getConverterName());

        $values = $parsedInput->getValues();
        $this->assertIsArray($values);

        $this->doTestValues($values);
    }

    public function testJsonConverter(): void
    {
        $content = $this->createMockData();
        $content = \json_encode($content);

        $request = Request::create('/', 'PUT', content: $content);
        $request->headers->set('content-type', 'application/json; charset=utf8');

        $inputConverter = $this->createInputConverter();
        $inputConverter->addConverter(new JsonConverter());

        $parsedInput = $inputConverter->convert($request);

        $this->assertInstanceOf(ParsedInput::class, $parsedInput);
        $this->assertEquals(JsonConverter::class, $parsedInput->getConverterName());

        $values = $parsedInput->getValues();
        $this->assertIsArray($values);

        $this->doTestValues($values);
    }

    public function testFormDataConverter(): void
    {
        $content = \file_get_contents(__DIR__.'/data/multipart.txt');

        $request = Request::create('/', 'PUT', content: $content);
        $request->headers->set('content-type', 'multipart/form-data; boundary=----------------------------abcdef');

        foreach ([false, true] as $fileSupport) {
            $inputConverter = $this->createInputConverter();
            $inputConverter->addConverter(new FormDataConverter(fileSupport: $fileSupport));

            $parsedInput = $inputConverter->convert($request);

            $this->assertInstanceOf(ParsedInput::class, $parsedInput);
            $this->assertEquals(FormDataConverter::class, $parsedInput->getConverterName());

            $values = $parsedInput->getValues();
            $this->assertIsArray($values);

            $this->doTestValues($values);

            $files = $parsedInput->getFiles();
            $this->assertIsArray($files);

            if (false === $fileSupport) {
                $this->assertEquals([], $files);
            } else {
                $this->assertArrayHasKey('image', $files);
                $this->assertIsArray($files['image']);

                $this->assertEquals(0, $files['image']['error']);
                $this->assertEquals('img.png', $files['image']['name']);
                $this->assertEquals('image/png', $files['image']['type']);
                $this->assertIsString($files['image']['tmp_name']);
                $this->assertEquals(16, $files['image']['size']);

                $this->assertEquals('random_junk_data', file_get_contents($files['image']['tmp_name']));
            }
        }
    }

    private function doTestValues(array $values): void
    {
        $this->assertArrayHasKey('key1', $values);
        $this->assertArrayHasKey('key2', $values);
        $this->assertArrayHasKey('nested', $values);

        $this->assertEquals('value1', $values['key1']);
        $this->assertEquals('value2', $values['key2']);

        $this->assertIsArray($values['nested']);
        $this->assertArrayHasKey(0, $values['nested']);
        $this->assertArrayHasKey('nestedKey1', $values['nested']);

        $this->assertEquals('nestedValue1', $values['nested'][0]);
        $this->assertEquals('nestedValue2', $values['nested']['nestedKey1']);
    }

    private function createMockData(): array
    {
        return [
            'key1' => 'value1',
            'key2' => 'value2',
            'nested' => [
                'nestedValue1',
                'nestedKey1' => 'nestedValue2',
            ],
        ];
    }

    private function createInputConverter(): InputConverter
    {
        return new InputConverter([
            new UrlEncodedConverter(),
        ]);
    }
}
