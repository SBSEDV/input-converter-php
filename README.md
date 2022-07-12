[![PHPStan](https://github.com/SBSEDV/input-converter-php/actions/workflows/phpstan.yml/badge.svg)](https://github.com/SBSEDV/input-converter-php/actions/workflows/phpstan.yml)
[![PHPUnit](https://github.com/SBSEDV/input-converter-php/actions/workflows/phpunit.yml/badge.svg)](https://github.com/SBSEDV/input-converter-php/actions/workflows/phpunit.yml)

# sbsedv/input-converter

A minimal PHP component to nativly support user input parsing on http methods other than POST.

PHP natively only supports the parsing of multipart/form-data and application/x-www-form-urlencoded on POST http requests.

Many modern web applications also want use / support a) other http methods
like PUT or PATCH and b) other content encodings like JSON or XML.

This component provides a very simple and extensible object oriented api to support just that.

Internally this component uses the PHP native functions [json_decode](https://www.php.net/manual/en/function.json-decode) and [parse_str](https://www.php.net/manual/en/function.parse-str) (multpart/form-data gets "translated" to x-www-form-urlencoded) and therefore complex data structures (arrays and objects) are only limited by what those functions support. <br/>
This effectifly means that HTMLForms like the following are `FULLY supported`.

```html
<form method="PUT">
    <select name="select[]" multiple>
        ...
    </select>

    <input type="text" name="text" />

    <input type="text" name="obj[key1]" />
    <input type="text" name="obj[key2]" />
    <select name="obj[key3][]" multiple>
        ...
    </select>
</form>
```

---

## **How it Works**

You should instantiate and call this component as early in your app lifecycle as possible.

You **MUST** pass either a [PSR-7](https://www.php-fig.org/psr/psr-7/) or [HTTP-Foundation](https://symfony.com/doc/current/components/http_foundation.html) request object to the "convert" method.

```php
<?php declare(strict_types=1);

use SBSEDV\InputConverter\InputConverter;
use SBSEDV\InputConverter\ParsedInput;

try {
    /** @var ParsedInput $parsedInput */
    $parsedInput = (new InputConverter())
        ->addConverter(...) // your converter instance
        ->convert($request);
} catch (MalformedContentException $e) {
    // a converter supported the request
    // but encountered an error while parsing

    http_status_code(400);
    exit();
} catch (UnsupportedRequestException) {
    // no converter supported the request
}

// access the data directly
$values = $parseInput->getValues(): array; // like $_POST
$files = $fileInput->getFiles(): array // like $_FILES

// and add them to your http-foundation / psr7 implementation
foreach ($parsedInput->getValues() as $key => $value) {
    // http-foundation
    $request = $request->request->set($key, $value);
}
```

---

## **Converters**

The actual parsing is handled by converter classes that implement
[SBSEDV\InputConverter\Converter\ConverterInterface](src/Converter/ConverterInterface.php).

You can always implement your own converter.

By default we support three customisable converters:

### `SBSEDV\InputConverter\Converter\UrlEncodedConverter`

Via its constructor you can influence which http methods it supports.

```php
public function __construct(
    array $methods = ['PUT', 'PATCH', 'DELETE']
);
```

---

### `SBSEDV\InputConverter\Converter\JsonConverter`

Via its constructor you can influence which content types and http methods it supports.

```php
public function __construct(
    array $contentTypes = ['application/json'],
    array $methods = ['POST', 'PUT', 'PATCH', 'DELETE']
);
```

---

### `SBSEDV\InputConverter\Converter\FormDataConverter`

Via its constructor you can influence which content types and http methods it supports.

Internally this uses the [riverline/multipart-parser](https://github.com/Riverline/multipart-parser) library for parsing.

```php
public function __construct(
    array $methods = ['PUT', 'PATCH', 'DELETE'],
    bool $fileSupport = false
);
```

#### **CAUTION WITH FILE UPLOADS**:

Even though file uploads via mulitpart/form-data are fully supported, they are **NOT** recommended because the whole file will be loaded into memory. You should instead use POST request for file uploads and let PHP handle that mess natively.

#### **_COMPATIBILITY_**:

Also, the returned file format is not compatibile with the native [$\_FILES](https://www.php.net/manual/en/features.file-upload.post-method.php#example-420) global.

If you upload an array / of images like:

```html
<input type="file" name="pictures[test1]" />
<input type="file" name="pictures[test2]" />
```

PHP has a very, lets say not friendly way of sorting the array:

```php
// Expected behaviour
$_FILES_ = [
    'pictures' => [
        'test1' => [
            'name' => 'test.png',
            'type' => 'image/png',
            'tmp_name' => '/tmp/123456',
            'error' => 0,
            'size' => 1234
        ],
        'test2' => [
            ...
        ]
    ]
];

// Actual behaviour
$_FILES_ = [
    'name' => [
        'test1' => 'what.png',
        'test2' => 'the_heck.jpg'
    ],
    'type' => [
        'test1' => 'image/png',
        'test2' => 'image/jpeg'
    ],
    ...
];
```

We return the much more friendly **expected** behaviour.
