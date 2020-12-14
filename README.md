# ChromiumPdf
This is a simple wrapper that lets you convert webpages or HTML into the PDF file. **Early preview**.

Documentation for library is practically, non-existing, at the moment. Here's quick example how it works:
```php
$chromiumPdf = new \Beganovich\ChromiumPdf\ChromiumPdf();

$chromiumPdf
    ->setChromiumPath('/usr/bin/google-chrome')
    ->setHtml('<h1>Hello world!</h1>')
    ->generate();
```

In case you want to convert web page into the PDF, you can use `setUrl()` instead of `setHtml()`:

```php
$chromiumPdf = new \Beganovich\ChromiumPdf\ChromiumPdf();

$chromiumPdf
    ->setChromiumPath('/usr/bin/google-chrome')
    ->setUrl('https://invoiceninja.com')
    ->generate();
```

### Requirements
- PHP ^7.3|^7.4|^8.0
- exec()
- Functional Google Chrome and/or Chromium.
- Linux or macOS

Windows isn't supported at the moment. In theory, it should work on WSL2, but it wasn't tested.
Stable release should include Windows support as well.

### Installation
Composer is required way of installing library:

```bash
composer require beganovich/chromium-pdf
```

### Comparison to Browsershot:
In case you need much more complex software to perform operations with headless browser go for [Spatie's Browsershot](https://github.com/spatie/browsershot). It's fantastic package.
Purpose of ChromiumPdf is to be really minimal & only let you make PDFs. Nothing beyond that.

Also, difference between ChromiumPdf and Browsershot - ChromiumPdf doesn't need Node to work.

### Credits
- [David Bomba](https://github.com/turbo124)
- [Benjamin Beganović](https://github.com/beganovich)
- [All contributors](https://github.com/beganovich/chromium-pdf/contributors)

### Licence
The MIT License (MIT).
