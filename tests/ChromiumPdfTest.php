<?php

namespace Test\ChromiumPdf;

use Beganovich\ChromiumPdf\ChromiumPdf;
use Beganovich\ChromiumPdf\Exception\MissingContent;
use PHPUnit\Framework\TestCase;

class ChromiumPdfTest extends TestCase
{
    public static $chromiumPath = '/usr/bin/google-chrome';

    public function testGeneratingPdfWorks()
    {
        $chromiumPdf = new ChromiumPdf();
        $html = file_get_contents(dirname(__DIR__, 1) . '/tests/template.html');

        $pdf = $chromiumPdf
            ->setChromiumPath(self::$chromiumPath)
            ->setHtml($html)
            ->generate();

        $this->assertNotNull($pdf);
    }

    public function testMissingContentShouldBeThrown()
    {
        $this->expectException(MissingContent::class);
        $this->expectExceptionMessage('No content provided. Make sure you call setHtml() or setUrl() before generate().');

        $chromiumPdf = new ChromiumPdf();

        $chromiumPdf
            ->setChromiumPath(self::$chromiumPath)
            ->generate();
    }

    public function testBuiltInChromiumShouldBeUsed()
    {
        $chromiumPdf = new ChromiumPdf();

        $this->assertEquals(dirname(__FILE__, 2) . '/versions/chrome', $chromiumPdf->getChromiumPath());
    }

    public function testUsingBuiltInChromium()
    {
        $chromiumPdf = new ChromiumPdf();

        $pdf = $chromiumPdf
            ->setHtml('<h1>Hello world!</h1>')
            ->generate();

        $this->assertNotNull($pdf);
    }
}
