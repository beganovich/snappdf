<p align="center">
    <img src="https://raw.githubusercontent.com/beganovich/snappdf/master/cover.png" alt="snappdf logo">
</p>

# snappdf

This is a simple library that lets you convert webpages or HTML into the PDF file using Chromium or Google Chrome.

- [snappdf](#snappdf)
    * [Usage](#usage)
    * [Speed](#speed)
    * [Requirements](#requirements)
    * [Installation](#installation)
        + [Downloading local Chromium](#downloading-local-chromium)
        + [Skip the Chromium download](#skip-the-chromium-download)
        + [Headless Chrome doesn't launch on UNIX](#headless-chrome-doesnt-launch-on-unix)
        + [Comparison to Browsershot](#comparison-to-browsershot)
    * [Credits](#credits)
    * [Licence](#licence)

## Usage

Here's quick example, how it works:

```php
$snappdf = new \Beganovich\Snappdf\Snappdf();

$pdf = $snappdf
    ->setHtml('<h1>Hello world!</h1>')
    ->save('/path/to/your/file.pdf');
```

In case you want to convert web page into the PDF, you can use `setUrl()` instead of `setHtml()`:

```php
$snappdf = new \Beganovich\Snappdf\Snappdf();

$pdf = $snappdf
    ->setUrl('https://github.com')
    ->save('/path/to/your/file.pdf');
```

.. if you need specific version of Chrome, or don't want to use locally downloaded Chromium, make use
of `setChromiumPath` method.

```php
$snappdf = new \Beganovich\Snappdf\Snappdf();

$pdf = $snappdf
    ->setUrl('https://github.com')
    ->setChromiumPath('/path/to/your/chrome')
    ->save('/path/to/your/file.pdf');
```

If none of previously listed option fits your needs, you can also set path to executable Chromium with environment
variable.

```bash
SNAPPDF_EXECUTABLE_PATH=/path/to/your/chrome
```

This is example for Nginx configuration (server block) (thanks [@cdahinten](https://github.com/beganovich/snappdf/issues/15#issuecomment-776135341)):

```
fastcgi_param SNAPPDF_EXECUTABLE_PATH '/usr/bin/chromium';
fastcgi_param SNAPPDF_SKIP_DOWNLOAD true;
```

If you need to generate PDF only, without saving it, make use of `generate()`:

```php
$snappdf = new \Beganovich\Snappdf\Snappdf();

$pdf = $snappdf
    ->setUrl('https://github.com')
    ->setChromiumPath('/path/to/your/chrome')
    ->generate();

file_put_contents('my.pdf', $pdf); // for local storage

Storage::disk('s3')->put('my.pdf', $pdf); // for remote storage
```

Note: `setChromiumPath` has highest priority. Second one is environment variable & third local download.

#### Command-line usage:

If you want to use snappdf as command-line tool, make use of "convert" command:

```bash
./vendor/bin/snappdf convert --url https://github.com /path/to/save.pdf
```

In case you want to convert HTML:

```bash
./vendor/bin/snappdf convert --html "<h1>Hello world!</h1>" /path/to/save.pdf
```

You can also specify custom binary location (if you don't use locally downloaded Chromium revision):

```bash
./vendor/bin/snappdf convert --url https://github.com --binary /usr/bin/google-chrome /path/to/save.pdf
```

## Speed

Main benefit and reason why this library exists is the speed of generating PDFs. It communicates directly with browser
itself and it takes less than .5s to generate PDFs (with cold start). This was tested on mid-range laptop with i5-5300U
and average SSD.

```bash
➜  snappdf git:(master) ./vendor/bin/phpunit --testdox --filter=testGeneratingPdfWorks
PHPUnit 9.5.0 by Sebastian Bergmann and contributors.

Snappdf (Test\Snappdf\Snappdf)
 ✔ Generating pdf works

Time: 00:00.199, Memory: 6.00 MB

OK (1 test, 1 assertion)
➜  snappdf git:(master) ./vendor/bin/phpunit --testdox --filter=testGeneratingPdfWorks
PHPUnit 9.5.0 by Sebastian Bergmann and contributors.

Snappdf (Test\Snappdf\Snappdf)
 ✔ Generating pdf works

Time: 00:00.171, Memory: 6.00 MB

OK (1 test, 1 assertion)
```

## Requirements

- PHP 7.3+

## Installation

Composer is recommended way of installing library:

```bash
composer require beganovich/snappdf
```

### Downloading local Chromium

snappdf can download & use local revision of Chromium. To achieve that, you can use:

```bash
./vendor/bin/snappdf download
```

You can find local downloads/revisions in `%projectRoot%/vendor/beganovich/snappdf/versions`.

Local revision will be used **only** when you don't provide path using `setChromiumPath()`.

**Note:** snappdf will download & use latest build of Chromium. Since Chromium itself doesn't have stable or unstable
release, browser itself can be buggy or possibly broken. We don't take any responsibility for that. **If security &
stability is your top priority, please install Google Chrome stable version & point package to use that.**

### Skip the Chromium download

If you need to dynamically skip the download, make use of `SNAPPDF_SKIP_DOWNLOAD` environment variable.

### Headless Chrome doesn't launch on UNIX

Make sure your system has installed all required dependencies.
Thanks [Puppeteer](https://github.com/puppeteer/puppeteer/blob/main/docs/troubleshooting.md#chrome-headless-doesnt-launch-on-unix)
❤

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

Note: You might need to install ‘libgbm-dev’ and ‘libxshmfence-dev’ also. This is reported for Ubuntu 20.04.
```

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

After installing dependencies you need to update nss library using this command

```
yum update nss -y
```

</details>

<details>
  <summary>Check out discussions</summary>

- [#290](https://github.com/puppeteer/puppeteer/issues/290) - Debian troubleshooting <br/>
- [#391](https://github.com/puppeteer/puppeteer/issues/391) - CentOS troubleshooting <br/>
- [#379](https://github.com/puppeteer/puppeteer/issues/379) - Alpine troubleshooting <br/>

</details>

### Comparison to Browsershot

In case you need much more complex software to perform operations with headless browser go
for [Spatie's Browsershot](https://github.com/spatie/browsershot). It's fantastic package. Purpose of snappdf is to be
really minimal & only focus on making PDFs.

Also, snappdf doesn't need Node installed to operate.

### Delay loading

You can use `waitBeforePrinting()` to set maximum delay before running the print. Use case for this would be if you need to
make an Ajax call or wait for library (e.g. charts) to load before printing.

**Note:**
Values provided are in milliseconds. One really important note is: If you delay load by 10 seconds (10000) it won't
delay PDF rendering itself by 10s, but it will give time for libraries or Ajax calls to finish & then action the
printing.

TLDR; If you set delay loading to 10 seconds & Ajax call takes 2 seconds to complete, PDF rendering will start
immediately after Ajax call is completed (after 2 seconds), and it won't wait 10 seconds.

## Credits

- [David Bomba](https://github.com/turbo124)
- [Benjamin Beganović](https://github.com/beganovich)
- [All contributors](https://github.com/beganovich/snappdf/contributors)

## Licence

The MIT License (MIT).
