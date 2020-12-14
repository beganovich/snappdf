<?php

namespace Test\ChromiumPdf;

use Beganovich\ChromiumPdf\ChromiumPdf;
use PHPUnit\Framework\TestCase;

class ChromiumPdfTest extends TestCase
{
    public function testGeneratingPdfWorks()
    {
        $path = '/usr/bin/google-chrome';
        $outputPath = dirname(__DIR__, 1) . '/example.pdf';

        $chromiumPdf = new ChromiumPdf();
        $html = file_get_contents(dirname(__DIR__, 1) . '/tests/template.html');

        $chromiumPdf
            ->setChromiumPath($path)
            ->setHtml($html)
            ->setOutputPath($outputPath)
            ->generate();

        $this->assertTrue(file_exists($chromiumPdf->getOutputPath()));
    }
}
