<?php declare(strict_types=1);

namespace SBSEDV\InputLibrary\Transformer;

use Riverline\MultiPartParser\Converters;
use SBSEDV\InputLibrary\ParsedInput;

class FormData extends AbstractTransformer
{
    protected const CONTENT_TYPE = 'multipart/form-data; boundary=';

    /** @var bool */
    protected $fileSupport;

    /**
     * @param string[] $methods     [optional] The supported http methods.
     * @param bool     $fileSupport [optional] Whether or not to support files uploads.
     *                              Please keep in mind that the entire files will be loaded
     *                              into memory which can cause problems with large file uploads.
     *                              You should use POST requests for file uploads and let PHP handle this.
     */
    public function __construct(
        array $methods = ['PUT', 'PATCH', 'DELETE'],
        bool $fileSupport = false
    ) {
        // prevent user from overwriting PHPs native parsing
        if (false !== ($key = array_search('POST', $methods, false))) {
            unset($methods[$key]);
        }

        $this->methods = $methods;
        $this->fileSupport = $fileSupport;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(): bool
    {
        // Because the actual content type is something along the lines of
        // "multipart/form-data; boundary=----WebKitFormBoundary1eomfOWEIFN"
        // we have to check if the content type starts with multipart/form-data

        return substr($this->getContentType(), 0, strlen(self::CONTENT_TYPE)) === self::CONTENT_TYPE && in_array($this->getMethod(), $this->methods);
    }

    /**
     * {@inheritdoc}
     */
    public function run(): ParsedInput
    {
        if (null === $this->request) {
            $document = Converters\Globals::convert();
        } elseif ($this->isHttpFoundation()) {
            $document = Converters\HttpFoundation::convert($this->request);
        } else {
            $document = Converters\PSR7::convert($this->request);
        }

        if (!$document->isMultiPart()) {
            return [];
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

                /**
                 * Create an array that represents $_FILES.
                 * Then json_encode it so that we can urlencode it.
                 */
                $body = json_encode([
                    'error' => UPLOAD_ERR_OK,
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

        // We urlencode the data structure so that we can keep complex data structures
        // because manual parsing is pretty hard and php only has a built in way
        // for x-www-urlencoded via parse_str.
        parse_str(implode('&', $strings), $stringArray);
        parse_str(implode('&', $files), $fileArray);

        // json_decode the $_FILES representation
        array_walk_recursive($fileArray, function (&$item) {
            $item = json_decode($item, true);
        });

        $parsedInput = new ParsedInput();
        $parsedInput->addValues($stringArray);
        $parsedInput->addFiles($fileArray);

        return $parsedInput;
    }
}
