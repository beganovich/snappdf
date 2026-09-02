<?php

namespace Test\Snappdf;

use Beganovich\Snappdf\Exception\MissingContent;
use Beganovich\Snappdf\Snappdf;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class SnappdfTest extends TestCase
{
    public function testGeneratingPdfWorks()
    {
        $snappdf = new Snappdf();
        $html = '<h1>Hello world</h1>';

        $pdf = $snappdf
            ->setHtml($html)
            ->generate();

        $this->assertNotNull($pdf);
    }

    public function testMissingContentShouldBeThrown()
    {
        $this->expectException(MissingContent::class);
        $this->expectExceptionMessage('No content provided. Make sure you call setHtml() or setUrl() before generate().');

        $snappdf = new Snappdf();

        $snappdf->generate();
    }

    public function testBuiltInChromiumShouldBeUsed()
    {
        $chromiumPdf = new Snappdf();

        $latestRevision = dirname(__FILE__, 2) . '/versions/revision.txt';

        if (!file_exists($latestRevision)) {
            $this->markTestSkipped('No Chromium binary found.');
        }

        if (!is_null(getenv('SNAPPDF_EXECUTABLE_PATH'))) {
            $this->markTestSkipped('Environmental Variable Set. Skipping test.');
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

    public function testWaitBeforePrintingArgumentIsSet()
    {
        $snappdf = new Snappdf();

        $snappdf->waitBeforePrinting(5);

        $this->assertEquals(5, $snappdf->getWaitTime());
        $this->assertContainsEquals('--virtual-time-budget=5', $snappdf->getChromiumArguments());
    }

    public function testArgumentCanBeAdded()
    {
        $snappdf = new Snappdf();

        $snappdf->addChromiumArguments('--virtual-time-budget=5');
        
        $this->assertNull($snappdf->getWaitTime());
        $this->assertContainsEquals('--virtual-time-budget=5', $snappdf->getChromiumArguments());
    }

    public function testWaitPrecidenceOverArgument()
    {
        $snappdf = new Snappdf();

        $snappdf->waitBeforePrinting(5)
            ->addChromiumArguments('--virtual-time-budget=20');

        $this->assertEquals(5, $snappdf->getWaitTime());
        $this->assertNotContainsEquals('--virtual-time-budget=20', $snappdf->getChromiumArguments());
        $this->assertContainsEquals('--virtual-time-budget=5', $snappdf->getChromiumArguments());
    }

    public function testArgumentsAreNotDuplicated()
    {
        $snappdf = new Snappdf();

        $snappdf->addChromiumArguments('--single-process')
            ->addChromiumArguments('--single-process');

        $this->assertEquals(1, array_count_values($snappdf->getChromiumArguments())['--single-process']);
    }

    public function testArgumentsCanBeCleared()
    {
        $snappdf = new Snappdf();

        $snappdf->clearChromiumArguments();

        $this->assertCount(0, $snappdf->getChromiumArguments());
    }

    public function testArgumentCanBeRemoved()
    {
        $snappdf = new Snappdf();

        $this->assertContains('--headless', $snappdf->getChromiumArguments());
        
        $argumentsCount = count($snappdf->getChromiumArguments());

        $snappdf->clearChromiumArgument('--headless');

        $this->assertNotContains('--headless', $snappdf->getChromiumArguments());

        $this->assertEquals($argumentsCount, count($snappdf->getChromiumArguments()) + 1);
    }

    public function testOrphanedChromiumProfilesAreRemovedAfterRun()
    {
        $snappdf = new Snappdf();

        $preExisting = sys_get_temp_dir() . DIRECTORY_SEPARATOR . '.org.chromium.Chromium.PREEXISTING';
        $orphan = sys_get_temp_dir() . DIRECTORY_SEPARATOR . '.org.chromium.Chromium.ORPHANED';

        $filesystem = new Filesystem();
        $filesystem->mkdir($preExisting);
        $filesystem->touch($preExisting . '/keep.txt');
        $filesystem->mkdir($orphan);

        try {
            // Chromium snapshots the profiles present before the run; an orphan created
            // during the run (abnormal exit) is absent from that snapshot and must go.
            $removeOrphaned = new \ReflectionMethod($snappdf, 'removeOrphanedChromiumProfiles');
            $removeOrphaned->invoke($snappdf, [$preExisting]);

            $this->assertTrue(is_dir($preExisting), 'Pre-existing .org.chromium profile must be preserved.');
            $this->assertFalse(is_dir($orphan), 'Orphaned profile from this run must be removed.');
        } finally {
            $filesystem->remove($preExisting);
            $filesystem->remove($orphan);
        }
    }
}
