<?php declare(strict_types=1);

namespace SBSEDV\Component\InputConverter\Converter;

use Psr\Http\Message\ServerRequestInterface;
use Riverline\MultiPartParser\Converters;
use SBSEDV\Component\InputConverter\Exception\MalformedContentException;
use SBSEDV\Component\InputConverter\ParsedInput;
use Symfony\Component\HttpFoundation\Request;

class FormData extends AbstractConverter
{
    /**
     * @param string[] $methods     [optional] The supported http methods.
     * @param bool     $fileSupport [optional] Whether to support files uploads.
     *                              Please keep in mind that all files will be loaded
     *                              into memory which can cause problems with large file uploads.
     *                              You should use POST requests for file uploads and let PHP handle this.
     */
    public function __construct(
        protected array $methods = ['PUT', 'PATCH', 'DELETE'],
        protected bool $fileSupport = false
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
        // The content type will always have a random suffix
        // "multipart/form-data; boundary=----WebKitFormBoundary4783NIJFN"

        return \in_array($request->getMethod(), $this->methods)
            && \str_starts_with($this->getContentType($request), 'multipart/form-data; boundary=');
    }

    /**
     * {@inheritdoc}
     */
    public function convert(Request | ServerRequestInterface $request): ParsedInput
    {
        if ($request instanceof Request) {
            $document = Converters\HttpFoundation::convert($request);
        } else {
            $document = Converters\PSR7::convert($request);
        }

        if (!$document->isMultiPart()) {
            throw new MalformedContentException();
        }

        $strings = $files = [];

        foreach ($document->getParts() as $part) {
            if ($part->isFile()) {
                if (!$this->fileSupport) {
                    continue;
                }

                // write the body to a temporary file
                $tmpFile = tempnam(sys_get_temp_dir(), '');
                $tmp = fopen($tmpFile, 'w');
                fwrite($tmp, $part->getBody());
                fclose($tmp);

                // Create an array that represents $_FILES.
                // Then json_encode it so that we can urlencode it.
                $body = json_encode([
                    'error' => \UPLOAD_ERR_OK,
                    'name' => $part->getFileName(),
                    'type' => $part->getMimeType(),
                    'tmp_name' => $tmpFile,
                    'size' => filesize($tmpFile),
                ]);

                $files[] = urlencode($part->getName()).'='.urlencode($body);
                continue;
            }

            $strings[] = urlencode($part->getName()).'='.urlencode($part->getBody());
        }

        // we have urlencoded the multipart data so that we can easily
        // keep complex data structures like arrays and objects that
        // are marked via "[]" on the part name.
        parse_str(implode('&', $strings), $valueArray);

        $parsedInput = new ParsedInput($valueArray);

        // json_decode the $_FILES representation
        if ($this->fileSupport) {
            parse_str(implode('&', $files), $fileArray);

            array_walk_recursive($fileArray, function (&$item) {
                $item = json_decode($item, true);
            });

            $parsedInput->addFiles($fileArray);
        }

        return $parsedInput;
    }
}
