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

        $latestRevision = dirname(__FILE__, 2) . '/versions/revision.txt';

        if (!file_exists($latestRevision)) {
            $this->markTestSkipped('No Chromium binary found.');
        }

        $latestRevision = file_get_contents($latestRevision);

        $this->assertEquals(dirname(__FILE__, 2) . "/versions/{$latestRevision}/chrome-linux/chrome", $chromiumPdf->getChromiumPath());
    }

    public function testUsingBuiltInChromium()
    {
        $snappdf = new Snappdf();

        $pdf = $snappdf
            ->setHtml('<h1>Hello world!</h1>')
            ->generate();

        $this->assertNotNull($pdf);
    }

    public function testSaveMethod()
    {
        $output = dirname(__FILE__, 2) . '/example.pdf';

        $snappdf = new Snappdf();

        $snappdf
            ->setHtml('<h1>Hello world!</h1>')
            ->save($output);

        $this->assertEquals('application/pdf', mime_content_type($output));

        unlink($output);
    }

    public function testFromWebsite()
    {
        $output = dirname(__FILE__, 2) . '/example.pdf';

        $snappdf = new Snappdf();

        $snappdf
            ->setUrl('https://expired.badssl.com/')
            ->save($output);

        $this->assertEquals('application/pdf', mime_content_type($output));

        unlink($output);
    }
}
