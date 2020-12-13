<?php

namespace Test\ChromiumPdf;

use Beganovich\ChromiumPdf\ChromiumPdf;
use PHPUnit\Framework\TestCase;

class ChromiumPdfTest extends TestCase
{
    /** @tests */
    public function generating_pdf_works()
    {
        $path = '/mnt/c/Program\ Files/Google/Chrome/Application/chrome.exe';

        $chromiumPdf = new ChromiumPdf();

        $chromiumPdf
            ->setChromiumPath($path)
            ->setUrl('https://google.com')
            ->setOutputPath('C:\Users\benja\example.pdf')
            ->generate();
    }
}
