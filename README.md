<p align="center">
    <img src="https://raw.githubusercontent.com/beganovich/snappdf/master/cover.png" alt="snappdf">
</p>

<p align="center">
    <a href="https://packagist.org/packages/beganovich/snappdf"><img src="https://img.shields.io/packagist/v/beganovich/snappdf?style=flat-square" alt="Packagist Version"></a>
    <a href="https://packagist.org/packages/beganovich/snappdf"><img src="https://img.shields.io/packagist/dt/beganovich/snappdf?style=flat-square" alt="Total Downloads"></a>
    <a href="https://github.com/beganovich/snappdf/actions/workflows/phpunit.yml"><img src="https://github.com/beganovich/snappdf/actions/workflows/phpunit.yml/badge.svg?branch=master&style=flat-square" alt="CI Status"></a>
    <a href="https://packagist.org/packages/beganovich/snappdf"><img src="https://img.shields.io/packagist/php-v/beganovich/snappdf?style=flat-square" alt="PHP Version"></a>
    <a href="https://github.com/beganovich/snappdf/blob/master/LICENSE"><img src="https://img.shields.io/github/license/beganovich/snappdf?style=flat-square" alt="License"></a>
</p>

# snappdf

A simple library that lets you convert webpages or HTML into PDF files using Chromium-powered browsers.

## Features

- **Fast** — Generates PDFs in under 0.5 seconds with cold start
- **No Node.js required** — Communicates directly with the browser
- **Minimal API** — Focused solely on PDF generation
- **Local Chromium** — Downloads and manages its own Chromium revision
- **Flexible configuration** — Custom binary path, arguments, and environment variables
- **Smart timing** — Virtual time budget for waiting on async content

## Requirements

- PHP ^8.2

## Installation

```bash
composer require beganovich/snappdf
```

## Quick Start

```php
$snappdf = new \Beganovich\Snappdf\Snappdf();

$pdf = $snappdf
    ->setHtml('<h1>Hello world!</h1>')
    ->save('/path/to/your/file.pdf');
```

## Usage

### From HTML

```php
$snappdf = new \Beganovich\Snappdf\Snappdf();

$pdf = $snappdf
    ->setHtml('<h1>Hello world!</h1>')
    ->save('/path/to/your/file.pdf');
```

### From URL

```php
$snappdf = new \Beganovich\Snappdf\Snappdf();

$pdf = $snappdf
    ->setUrl('https://github.com')
    ->save('/path/to/your/file.pdf');
```

### Custom Chromium Path

```php
$snappdf = new \Beganovich\Snappdf\Snappdf();

$pdf = $snappdf
    ->setUrl('https://github.com')
    ->setChromiumPath('/path/to/your/chrome')
    ->save('/path/to/your/file.pdf');
```

### Laravel with Blade

```php
$snappdf = new \Beganovich\Snappdf\Snappdf();

$html = view('pdf.warranty', $fields)->render();

$pdf = $snappdf
    ->setHtml($html)
    ->save('/path/to/your/file.pdf');
```

### Symfony Filesystem

```php
use Symfony\Component\Filesystem\Filesystem;

$snappdf = new \Beganovich\Snappdf\Snappdf();

$pdf = $snappdf
    ->setHtml('<h1>Hello world!</h1>')
    ->generate();

$filesystem = new Filesystem();
$filesystem->dumpFile('/path/to/your/file.pdf', $pdf);
```

### Environment Variable

```bash
SNAPPDF_EXECUTABLE_PATH=/path/to/your/chrome
```

Nginx example:

```
fastcgi_param SNAPPDF_EXECUTABLE_PATH '/usr/bin/chromium';
fastcgi_param SNAPPDF_SKIP_DOWNLOAD true;
```

### Generate Without Saving

```php
$snappdf = new \Beganovich\Snappdf\Snappdf();

$pdf = $snappdf
    ->setUrl('https://github.com')
    ->generate();

file_put_contents('my.pdf', $pdf);
Storage::disk('s3')->put('my.pdf', $pdf);
```

### Stream to Browser

```php
$snappdf = new \Beganovich\Snappdf\Snappdf();

$pdf = $snappdf
    ->setHtml('<h1>Hello world!</h1>')
    ->generate();

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="document.pdf"');
echo $pdf;
```

**Priority:** `setChromiumPath()` takes precedence, then `SNAPPDF_EXECUTABLE_PATH`, then local Chromium download.

## Configuration

### Chromium Arguments

```php
$snappdf = new \Beganovich\Snappdf\Snappdf();

$snappdf
    ->setUrl('https://github.com')
    ->addChromiumArguments('--single-process --tls1')
    ->generate();
```

Remove a single argument:

```php
$snappdf->getChromiumArguments();
// ['--headless', '--disable-gpu', '--disable-translations']

$snappdf->clearChromiumArgument('--headless');
// ['--disable-gpu', '--disable-translations']
```

Override all default arguments with the `SNAPPDF_EXECUTABLE_ARGUMENTS` environment variable.

> **Note:** `--print-to-pdf` is always added. `--virtual-time-budget` is added when `waitBeforePrinting()` is called.

Clear all arguments:

```php
$snappdf->setChromiumPath('/path/to/your/chrome')
    ->clearChromiumArguments();

// $snappdf->getChromiumArguments() = []
```

## CLI Usage

```bash
# Convert a URL
./vendor/bin/snappdf convert --url https://github.com /path/to/save.pdf

# Convert raw HTML
./vendor/bin/snappdf convert --html '<h1>Hello world!</h1>' /path/to/save.pdf

# With custom binary
./vendor/bin/snappdf convert --url https://github.com --binary /usr/bin/google-chrome /path/to/save.pdf
```

## Speed

snappdf communicates directly with the browser, generating PDFs in under 0.5 seconds with cold start on mid-range hardware (i5-5300U, SSD).

```
./vendor/bin/phpunit --testdox --filter=testGeneratingPdfWorks
PHPUnit 11.4.0 by Sebastian Bergmann and contributors.

Snappdf (Test\Snappdf\Snappdf)
 ✔ Generating pdf works

Time: 00:00.171, Memory: 6.00 MB
```

## Chromium Management

### Local Download

```bash
./vendor/bin/snappdf download
```

Downloads are stored in `vendor/beganovich/snappdf/versions`. The local revision is used only when no path is provided via `setChromiumPath()`.

> **Note:** snappdf downloads the latest Chromium build. Since Chromium has no stable or unstable releases, the browser may occasionally be buggy. For production environments, install Google Chrome stable and point the package to it.

### Skip Download

Set the `SNAPPDF_SKIP_DOWNLOAD` environment variable to skip downloading Chromium.

### Troubleshooting

If Chrome doesn't launch on UNIX, ensure the required system dependencies are installed. See [Puppeteer's troubleshooting guide](https://github.com/puppeteer/puppeteer/blob/main/docs/troubleshooting.md#chrome-headless-doesnt-launch-on-unix).

<details>
<summary>Debian (e.g. Ubuntu)</summary>

```
ca-certificates
fonts-liberation
libappindicator3-1
libasound2
libatk-bridge2.0-0
libatk1.0-0
libc6
libcairo2
libcups2
libdbus-1-3
libexpat1
libfontconfig1
libgbm1
libgcc1
libglib2.0-0
libgtk-3-0
libnspr4
libnss3
libpango-1.0-0
libpangocairo-1.0-0
libstdc++6
libx11-6
libx11-xcb1
libxcb1
libxcomposite1
libxcursor1
libxdamage1
libxext6
libxfixes3
libxi6
libxrandr2
libxrender1
libxss1
libxtst6
lsb-release
wget
xdg-utils
```

Note: You might also need `libgbm-dev` and `libxshmfence-dev` (reported for Ubuntu 20.04).

</details>

<details>
<summary>CentOS</summary>

```
alsa-lib.x86_64
atk.x86_64
cups-libs.x86_64
gtk3.x86_64
ipa-gothic-fonts
libXcomposite.x86_64
libXcursor.x86_64
libXdamage.x86_64
libXext.x86_64
libXi.x86_64
libXrandr.x86_64
libXScrnSaver.x86_64
libXtst.x86_64
pango.x86_64
xorg-x11-fonts-100dpi
xorg-x11-fonts-75dpi
xorg-x11-fonts-cyrillic
xorg-x11-fonts-misc
xorg-x11-fonts-Type1
xorg-x11-utils
```

After installing dependencies, update nss:

```
yum update nss -y
```

</details>

<details>
<summary>Related discussions</summary>

- [#290](https://github.com/puppeteer/puppeteer/issues/290) — Debian troubleshooting
- [#391](https://github.com/puppeteer/puppeteer/issues/391) — CentOS troubleshooting
- [#379](https://github.com/puppeteer/puppeteer/issues/379) — Alpine troubleshooting

</details>

## Smart Delay

Use `waitBeforePrinting()` to set a maximum delay (in milliseconds) before printing. This is useful for waiting on AJAX calls or chart libraries to finish loading.

```php
$snappdf = new \Beganovich\Snappdf\Snappdf();

$pdf = $snappdf
    ->setUrl('https://github.com')
    ->waitBeforePrinting(10000)
    ->generate();
```

If your AJAX call completes in 2 seconds, rendering starts immediately — it won't wait the full 10 seconds.

## Temporary Files

Since version 3, snappdf cleans up temporary files automatically. To keep them:

```php
$snappdf = new \Beganovich\Snappdf\Snappdf();

$pdf = $snappdf
    ->setUrl('https://github.com')
    ->setKeepTemporaryFiles(true)
    ->generate();
```

## Comparison to Browsershot

For more complex headless browser operations, use [Spatie's Browsershot](https://github.com/spatie/browsershot). snappdf is intentionally minimal — it only does PDFs and doesn't require Node.js.

## Testing

```bash
composer tests
```

## Credits

- [David Bomba](https://github.com/turbo124)
- [Benjamin Beganović](https://github.com/beganovich)
- [All contributors](https://github.com/beganovich/snappdf/contributors)

## License

The MIT License (MIT). See [LICENSE](LICENSE) for more information.
