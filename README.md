# sbsedv/input-library

A minimal PHP library to nativly support PUT, PATCH and DELETE user input in form of mutlipart/form-data, application/x-www-urlencoded and various JSON types like application/json with complex data structures.

Normally, PHP only parses multipart/form-data and application/x-www-urlencoded on POST requests and does nothing with application/json.
Many libraries, like [symfony/http-foundation](https://symfony.com/doc/current/components/http_foundation.html) with `$request->toArray()`, have limited support for JSON and usually, but not always, only work on POST requests.

The default workarounds are to use the "X-HTTP-METHOD-OVERRIDE" header or a hidden "_method" input field to send requests as method=POST that the server then knows to translate to eg. PUT or PATCH requests.

This library provides all you need to nativly support these kinds of requests, including complex data structures like nested Arrays and Objects. Because internally this library uses the PHP native functions [json_decode](https://www.php.net/manual/en/function.json-decode) and [parse_str](https://www.php.net/manual/en/function.parse-str) (multpart/form-data gets translated to x-www-urlencoded), complex data structures are only limited by what those functions support.

---

## **How it Works**

You should call this library as early as possible in your application.

You **CAN** pass a [PSR-7](https://www.php-fig.org/psr/psr-7/) or [HTTP-Foundation](https://symfony.com/doc/current/components/http_foundation.html) to the constructor and the library will make use of that.
If you do not pass anything to the constructor, the library uses PHPs `$_SERVER` global.

```php
<?php declare(strict_types=1);

use SBSEDV\InputLibrary\InputLibrary;
use SBSEDV\InputLibrary\Transformer\FormData;
use SBSEDV\InputLibrary\Transformer\JSON;
use SBSEDV\InputLibrary\Transformer\UrlEncoded;

// PUT /test HTTP/1.1
// HOST: example.com
// Content-Type: application/json
// {"key": "value", "array": ["value1","value2"]}
//
// === OR ===
//
// Content-Type: application/x-www-urlencoded
// key=value&array[]=value1&array[]=value2
//
// === OR ===
//
// Content-Type: multipart/form-data; boundary=----WebKitFormBoundary1fownfown
// ------WebKitFormBoundary1fownfown
// Content-Disposition: form-data; name="key"
// value
// ------WebKitFormBoundary1fownfown
// Content-Disposition: form-data; name="array[]"
// value1
// ------WebKitFormBoundary1fownfown
// Content-Disposition: form-data; name="array[]"
// value2

/**
 * You can pass an instance of
 * Psr\Http\Message\ServerRequestInterface
 *                  OR
 * Symfony\Component\HttpFoundation\Request
 * to the constructor.
 *
 * Otherwise the library uses PHP globals.
 *
 * You can als set / remove the request object
 * via setRequest in case you are having problems
 * with your container builder.
 *
 * @return SBSEDV\InputLibrary\ParsedInput
 */
$parseInput = (new InputLibrary())
    ->registerTransformers(
        new JSON([
            'application/json',
            'application/ld+json'
        ]), // support multiple json types
        new UrlEncoded(['PUT', 'PATCH']), // allow only PUT and PATCH
        new FormData() // default options
    )->run();

// update $_POST and $_FILES with parsed values
$parseInput->toGlobals(): void;
// populate $request->reqst and $request->files
$parseInput->toHttpFoundation(
    Symfony\Component\HttpFoundation\Request $request
): void;

// or access the data directly
$values = $parseInput->getValues(): array; // like $_POST
$files = $fileInput->getFiles(): array // like $_FILES
```

---

### **CAUTION WITH FILE UPLOADS**:

Even though file uploads via mulitpart/form-data are fully supported, they are **NOT** recommended because the whole file will be loaded into memory which may become a problem with large files and shared hosting plans. You should instead use POST request for file uploads and let PHP handle that mess.

#### ***COMPATIBILITY***:

The returned file format is not 100 percent compatibile with the native [$_FILES](https://www.php.net/manual/en/features.file-upload.post-method.php#example-420) global.

If you upload an array / of images like:

```html
<input type="file" name="pictures[test1]" />
<input type="file" name="pictures[test2]" />
```

PHP has a very, lets say not friendly way of arranging the array:

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

For sanity reasons, this library returns the **expected** behaviour.

---

## **Transformers**

The actual parsing and extracting is handled by `Transformer` classes that implement `SBSEDV\InputLibrary\TransformerInterface`.

You can always implement your own transformer to handle your custom content types and register it with the library via the `SBSEDV\InputLibrary\InputLibrary::registerTransformer` method.

By default, this library comes with three transformers:

##### `SBSEDV\InputLibrary\Transformer\UrlEncoded`

This transformer handles application/x-www-urlencoded.
Under the hood, this uses PHPs native [parse_str](https://www.php.net/manual/en/function.parse-str) function.
Via its constructor you can influence on which methods it should work.

```php
// SBSEDV\InputLibrary\Transformer\UrlEncoded

public function __construct(
    array $methods = ['PUT', 'PATCH', 'DELETE']
);
```

##### `SBSEDV\InputLibrary\Transformer\JSON`

This transformer handles JSON.
Internally this uses PHPs native [json_decode](https://www.php.net/manual/en/function.json-decode) function.
Via its constructor you can influence on which content-types and methods it should work.

Because PHP does not parse JSON nativly, this also registers for the POST http method.

```php
// SBSEDV\InputLibrary\Transformer\JSON

public function __construct(
    array $contentTypes = ['application/json'],
    array $methods = ['POST', 'PUT', 'PATCH', 'DELETE']
);
```

##### `SBSEDV\InputLibrary\Transformer\FormData`

This transformer handles mulitpart/form-data and is by far the most complicated, but also the most convenient because of JavaScripts awesome [FormData API](https://developer.mozilla.org/en-US/docs/Web/API/FormData).

Internally this uses the awesome [riverline/multipart-parser](https://github.com/Riverline/multipart-parser) library for parsing. Then, those key-value pairs are concatenated to a x-www-urlencoded string and then passed to the native [parse_str](https://www.php.net/manual/en/function.parse-str) function to keep complex data structures.


Via its constructor you can influence on which methods it should work and if file uploads should be supported (defaults to false).

```php
// SBSEDV\InputLibrary\Transformer\FormData

public function __construct(
    array $methods = ['PUT', 'PATCH', 'DELETE'],
    bool $fileSupport = false
);
```
