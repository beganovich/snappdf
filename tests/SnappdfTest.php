<?php

namespace Test\Snappdf;

use Beganovich\Snappdf\Exception\MissingContent;
use Beganovich\Snappdf\Snappdf;
use PHPUnit\Framework\TestCase;

class SnappdfTest extends TestCase
{
    public static $chromiumPath = '/usr/bin/google-chrome';

    public function testGeneratingPdfWorks()
    {
        $snappdf = new Snappdf();
        $html = '<h1>Hello world</h1>';

        $pdf = $snappdf
            ->setChromiumPath(self::$chromiumPath)
            ->setHtml($html)
            ->generate();

        $this->assertNotNull($pdf);
    }

    public function testMissingContentShouldBeThrown()
    {
        $this->expectException(MissingContent::class);
        $this->expectExceptionMessage('No content provided. Make sure you call setHtml() or setUrl() before generate().');

        $snappdf = new Snappdf();

        $snappdf
            ->setChromiumPath(self::$chromiumPath)
            ->generate();
    }

    public function testBuiltInChromiumShouldBeUsed()
    {
        $chromiumPdf = new Snappdf();

        $this->assertEquals(dirname(__FILE__, 2) . '/versions/chrome', $chromiumPdf->getChromiumPath());
    }

    public function testUsingBuiltInChromium()
    {
        $snappdf = new Snappdf();

        $pdf = $snappdf
            ->setHtml('<h1>Hello world!</h1>')
            ->generate();

        $this->assertNotNull($pdf);
    }
}
