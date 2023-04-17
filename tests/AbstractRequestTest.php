<?php declare(strict_types=1);

namespace SBSEDV\InputConverter\Tests;

use PHPUnit\Framework\TestCase;
use SBSEDV\InputConverter\Converter\UrlEncodedConverter;
use SBSEDV\InputConverter\InputConverter;

abstract class AbstractRequestTest extends TestCase
{
    protected function doTestValues(array $values): void
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

    protected function createMockData(): array
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

    protected function createInputConverter(): InputConverter
    {
        return new InputConverter([
            new UrlEncodedConverter(),
        ]);
    }
}
