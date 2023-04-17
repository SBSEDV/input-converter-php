<?php declare(strict_types=1);

namespace SBSEDV\InputConverter\Tests;

use SBSEDV\InputConverter\Converter\FormDataConverter;
use SBSEDV\InputConverter\Converter\JsonConverter;
use SBSEDV\InputConverter\Request\HttpFoundationRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class HttpFoundationRequestTest extends AbstractRequestTest
{
    public function testUrlEncodedConverter(): void
    {
        $content = $this->createMockData();
        $content = \http_build_query($content);

        $request = Request::create('/', 'PUT', content: $content);
        $request->headers->set('content-type', 'application/x-www-form-urlencoded');

        $inputConverter = $this->createInputConverter();

        $inputConverter->convert(new HttpFoundationRequest($request));

        $this->doTestValues($request->request->all());
    }

    public function testJsonConverter(): void
    {
        $content = $this->createMockData();
        $content = \json_encode($content);

        $request = Request::create('/', 'PUT', content: $content);
        $request->headers->set('content-type', 'application/json; charset=utf8');

        $inputConverter = $this->createInputConverter();
        $inputConverter->addConverter(new JsonConverter());

        $inputConverter->convert(new HttpFoundationRequest($request));

        $this->doTestValues($request->request->all());
    }

    public function testFormDataConverter(): void
    {
        $content = \file_get_contents(__DIR__.'/data/multipart.txt');

        $request = Request::create('/', 'PUT', content: $content);
        $request->headers->set('content-type', 'multipart/form-data; boundary=----------------------------abcdef');

        foreach ([false, true] as $fileSupport) {
            $inputConverter = $this->createInputConverter();
            $inputConverter->addConverter(new FormDataConverter(fileSupport: $fileSupport));

            $inputConverter->convert(new HttpFoundationRequest($request));

            $this->doTestValues($request->request->all());

            if (false === $fileSupport) {
                $this->assertEquals([], $request->files->all());
            } else {
                $this->assertArrayHasKey('image', $request->files->all());

                /** @var UploadedFile */
                $file = $request->files->get('image');

                $this->assertEquals(0, $file->getError());
                $this->assertEquals('img.png', $file->getClientOriginalName());
                $this->assertEquals('image/png', $file->getClientMimeType());
                $this->assertIsString($file->getRealPath());
                $this->assertEquals(16, $file->getSize());

                $this->assertEquals('random_junk_data', \file_get_contents($file->getRealPath()));
            }
        }
    }
}
