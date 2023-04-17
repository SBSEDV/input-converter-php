# CHANGELOG

This changelog references the relevant changes (bug and security fixes).

To get the diff between the latest two versions, go to https://github.com/sbsedv/input-converter-php/compare/v2.0.1...v3.0.0

-   3.0.0 (2023-04-17)

    -   Use `ReqeustInterface` objects internally
    -   Populate Psr7 and Http-Foundation request with data directly

-   2.0.0 (2022-07-10)

    -   Updated namespace from `SBSEDV\Component\InputConverter` to `SBSEDV\InputConverter`
    -   Added `Converter` suffix to converter classes
    -   Added `ParsedInput::getConverterName()` to get the FQCN of the used converter.
    -   Removed built in support for dynamic content types with the UrlEncodedConverter
        If you require this feature, just extend it and overwrite the 'supports' method.
