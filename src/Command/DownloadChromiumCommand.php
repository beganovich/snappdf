<?php


namespace Beganovich\Snappdf\Command;

use Beganovich\Snappdf\Exception\PlatformNotSupported;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use ZipArchive;

class DownloadChromiumCommand extends Command
{
    /**
     * Command signature.
     *
     * @var string
     */
    protected static $defaultName = 'download';

    /**
     * Download link for revisions.
     *
     * @var string
     */
    public $revisionUrl = 'https://download-chromium.appspot.com/rev/%s?type=snapshots';

    /**
     * Configure command properties.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Downloads a latest version of Chromium');
    }

    /**
     * The main command execute method.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \Beganovich\Snappdf\Exception\PlatformNotSupported
     * @throws \Throwable
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (getenv('SNAPPDF_SKIP_DOWNLOAD') == 'true') {
            $output->writeln('SNAPPDF_SKIP_DOWNLOAD variable found. Skipping the download.');

            return Command::SUCCESS;
        }

        $response = json_decode(
            file_get_contents(sprintf($this->revisionUrl, $this->generatePlatformCode()))
        );

        $latestVersion = $response->content;

        $output->writeln('Starting download. Revision: ' . $latestVersion);

        $platformRevision = "{$latestVersion}-{$this->generatePlatformCode()}";

        if (!file_exists(dirname(__FILE__, 3) . "/versions/{$platformRevision}")) {
            file_put_contents(
                dirname(__FILE__, 3) . "/versions/{$platformRevision}.zip",
                fopen("https://download-chromium.appspot.com/dl/{$this->generatePlatformCode()}?type=snapshots", 'r')
            );
        }

        $output->writeln('Download completed. Extracting the zip archive.');

        $archive = new ZipArchive();

        if ($archive->open(dirname(__FILE__, 3) . "/versions/{$platformRevision}.zip")) {
            mkdir(dirname(__FILE__, 3) . "/versions/{$platformRevision}");

            $archive->extractTo(dirname(__FILE__, 3) . "/versions/{$platformRevision}");
            $archive->close();
        }

        $output->writeln('Archive extracted.');

        file_put_contents(dirname(__FILE__, 3) . '/versions/revision.txt', $platformRevision);

        chmod($this->generatePlatformExecutable($platformRevision), 0755);

        (new Filesystem())->remove(dirname(__FILE__, 3) . "/versions/{$platformRevision}.zip");

        $output->writeln("Completed! {$platformRevision} currently in use.");

        return Command::SUCCESS;
    }

    /**
     * Generate transformed platform codename for "appspot.com" download.
     *
     * @throws \Beganovich\Snappdf\Exception\PlatformNotSupported
     * @return string
     */
    public function generatePlatformCode(): string
    {
        if (PHP_OS == 'Linux') {
            return 'Linux_x64';
        }

        if (PHP_OS == 'Darwin') {
            return 'Mac';
        }

        if (stripos(PHP_OS, 'WIN') === 0) {
            return PHP_INT_SIZE == 4 ? 'Win' : 'Win_x64';
        }

        throw new PlatformNotSupported('Platform ' . PHP_OS . ' is not supported.');
    }

    /**
     * Generate platform executable path based on generatePlatformCode().
     *
     * @param string $revision
     * @param int $level
     *
     * @throws \Beganovich\Snappdf\Exception\PlatformNotSupported
     * @return null|string
     */
    public function generatePlatformExecutable(string $revision, int $level = 3): ?string
    {
        $platform = $this->generatePlatformCode();

        if ($platform == 'Linux_x64') {
            return dirname(__FILE__, $level) . "/versions/{$revision}/chrome-linux/chrome";
        }

        if ($platform == 'Mac') {
            return dirname(__FILE__, $level) . "/versions/{$revision}/chrome-mac/Chromium.app";
        }

        if ($platform == 'Win' || $platform == 'Win_x64') {
            return dirname(__FILE__, $level) . "/versions/{$revision}/chrome-win/chrome.exe";
        }
    }
}
