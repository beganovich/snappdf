<?php

namespace Test\ChromiumPdf;

use Beganovich\ChromiumPdf\ChromiumPdf;
use Beganovich\ChromiumPdf\Exception\GeneratingPdfWasUnsuccessful;
use PHPUnit\Framework\TestCase;

class ChromiumPdfTest extends TestCase
{
    public function testGeneratingPdfWorks()
    {
        $path = '/usr/bin/google-chrome';
        $url = 'http://invoiceninja.com';
        $outputPath = dirname(__DIR__, 1) . '/example.pdf';

        $chromiumPdf = new ChromiumPdf();

        $chromiumPdf
            ->setChromiumPath($path)
            ->setUrl($url)
            ->setOutputPath($outputPath)
            ->generate();

        $this->assertTrue(file_exists($chromiumPdf->getOutputPath()));
    }
}
