<?php declare(strict_types=1);

namespace SBSEDV\InputConverter\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\UploadedFileInterface;
use SBSEDV\InputConverter\Converter\FormDataConverter;
use SBSEDV\InputConverter\Converter\JsonConverter;
use SBSEDV\InputConverter\Request\Psr7Request;

class Psr7RequestTest extends AbstractRequestTest
{
    public function testUrlEncodedConverter(): void
    {
        $content = $this->createMockData();
        $content = \http_build_query($content);

        $request = new ServerRequest('PUT', '/', [
            'content-type' => 'application/x-www-form-urlencoded',
        ], $content);

        $inputConverter = $this->createInputConverter();

        $request = new Psr7Request($request);

        $inputConverter->convert($request);

        $this->doTestValues($request->getRequest()->getParsedBody());
    }

    public function testJsonConverter(): void
    {
        $content = $this->createMockData();
        $content = \json_encode($content);

        $request = new ServerRequest('PUT', '/', [
            'content-type' => 'application/json; charset=utf8',
        ], $content);

        $inputConverter = $this->createInputConverter();
        $inputConverter->addConverter(new JsonConverter());

        $request = new Psr7Request($request);

        $inputConverter->convert($request);

        $this->doTestValues($request->getRequest()->getParsedBody());
    }

    public function testFormDataConverter(): void
    {
        $content = \file_get_contents(__DIR__.'/data/multipart.txt');

        foreach ([false, true] as $fileSupport) {
            $inputConverter = $this->createInputConverter();
            $inputConverter->addConverter(new FormDataConverter(fileSupport: $fileSupport));

            $request = new ServerRequest('PUT', '/', [
                'content-type' => 'multipart/form-data; boundary=----------------------------abcdef',
            ], $content);

            if ($fileSupport) {
                $request = new Psr7Request($request, new Psr17Factory(), new Psr17Factory());
            } else {
                $request = new Psr7Request($request);
            }

            $inputConverter->convert($request);

            $this->doTestValues($request->getRequest()->getParsedBody());

            $uploadedFiles = $request->getRequest()->getUploadedFiles();

            if (false === $fileSupport) {
                $this->assertEquals([], $uploadedFiles);
            } else {
                $this->assertCount(1, $uploadedFiles);

                /** @var UploadedFileInterface */
                $file = $uploadedFiles[0];

                $this->assertEquals(0, $file->getError());
                $this->assertEquals('img.png', $file->getClientFilename());
                $this->assertEquals('image/png', $file->getClientMediaType());
                $this->assertEquals(16, $file->getStream()->getSize());

                $this->assertEquals('random_junk_data', (string) $file->getStream());
            }
        }
    }
}
