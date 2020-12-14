<?php

namespace Test\ChromiumPdf;

use Beganovich\ChromiumPdf\ChromiumPdf;
use PHPUnit\Framework\TestCase;

class ChromiumPdfTest extends TestCase
{
    public function testGeneratingPdfWorks()
    {
        $path = '/usr/bin/google-chrome';

        $chromiumPdf = new ChromiumPdf();
        $html = file_get_contents(dirname(__DIR__, 1) . '/tests/template.html');

        $pdf = $chromiumPdf
            ->setChromiumPath($path)
            ->setHtml($html)
            ->generate();

        $this->assertTrue(file_exists($pdf));
    }
}
