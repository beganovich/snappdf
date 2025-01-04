<?php


namespace Beganovich\Snappdf\Command;

use Beganovich\Snappdf\Exception\PlatformNotSupported;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use ZipArchive;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Command signature.
 *
 */
#[AsCommand(
    name: 'download',
    description: 'Downloads the chromium binary',
    hidden: false,
    aliases: ['download:chromium']
)]
class DownloadChromiumCommand extends Command
{


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
        $this
            ->setDescription('Downloads a latest version of Chromium')
            ->addOption('force', null, InputOption::VALUE_NONE);
    }

    /**
     * The main command execute method.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     * @throws \Throwable
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     * @throws \Beganovich\Snappdf\Exception\PlatformNotSupported
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (getenv('SNAPPDF_SKIP_DOWNLOAD') == 'true') {
            $output->writeln('SNAPPDF_SKIP_DOWNLOAD variable found. Skipping the download.');

            return Command::SUCCESS;
        }

        if (file_exists(dirname(__FILE__, 3) . '/versions/revision.txt')) {
            if (!$input->getOption('force')) {
                $output->writeln('Chromium binary already downloaded. Provide --force to download again.');

                return Command::SUCCESS;
            }
        }

        if(PHP_OS == 'Linux')
            return $this->getUngoogled($output);

        $response = json_decode(
            file_get_contents(sprintf($this->revisionUrl, $this->generatePlatformCode()))
        );

        $latestVersion = $response->content;

        $output->writeln('Starting download. Revision: ' . $latestVersion);

        $platformRevision = "{$latestVersion}-{$this->generatePlatformCode()}";

        if (file_exists(dirname(__FILE__, 3) . "/versions/{$platformRevision}")) {
            $output->writeln('Latest revision already downloaded.');

            return Command::SUCCESS;
        }

        file_put_contents(
            dirname(__FILE__, 3) . "/versions/{$platformRevision}.zip",
            fopen("https://download-chromium.appspot.com/dl/{$this->generatePlatformCode()}?type=snapshots", 'r')
        );

        $output->writeln('Download completed. Extracting the zip archive.');

        $archive = new ZipArchive();

        if ($archive->open(dirname(__FILE__, 3) . "/versions/{$platformRevision}.zip")) {
            mkdir(dirname(__FILE__, 3) . "/versions/{$platformRevision}");

            $archive->extractTo(dirname(__FILE__, 3) . "/versions/{$platformRevision}");
            $archive->close();
        }

        $output->writeln('Archive extracted.');

        file_put_contents(dirname(__FILE__, 3) . '/versions/revision.txt', $platformRevision);

        chmod(dirname(__FILE__, 3) . '/versions/ungoogled/chrome-linux/chrome', 0755);
        chmod(dirname(__FILE__, 3) . '/versions/ungoogled/chrome-linux/chrome_crashpad_handler', 0755);

        $this->setCrashpadExecutable($platformRevision);

        (new Filesystem())->remove(dirname(__FILE__, 3) . "/versions/{$platformRevision}.zip");

        $output->writeln("Completed! {$platformRevision} currently in use.");

        return Command::SUCCESS;
    }

    private function getUngoogled($output)
    {

        $output->writeln('Starting download. Ungoogled Chrome');

        $platformRevision = 'ungoogled';
        $url = 'https://pdf.invoicing.co/ungoogled.tar';

        file_put_contents(
            dirname(__FILE__, 3) . "/versions/ungoogled.tar",
            fopen($url, 'r')
        );

        $output->writeln('Extracting');
        mkdir(dirname(__FILE__, 3) . "/versions/{$platformRevision}");

        $phar = new \PharData(dirname(__FILE__, 3) . "/versions/ungoogled.tar");
        $phar->extractTo(dirname(__FILE__, 3) . "/versions/{$platformRevision}", null, true);

        unlink(dirname(__FILE__, 3) . "/versions/ungoogled.tar");

        $output->writeln('Archive extracted.');

        file_put_contents(dirname(__FILE__, 3) . '/versions/revision.txt', $platformRevision);

        chmod(dirname(__FILE__, 3) . '/versions/ungoogled/chrome-linux/chrome', 0755);
        chmod(dirname(__FILE__, 3) . '/versions/ungoogled/chrome-linux/chrome_crashpad_handler', 0755);

        $output->writeln("Completed! {$platformRevision} currently in use.");

        return Command::SUCCESS;

    }

    /**
     * Generate transformed platform codename for "appspot.com" download.
     *
     * @return string
     * @throws \Beganovich\Snappdf\Exception\PlatformNotSupported
     */
    public function generatePlatformCode(): string
    {
        if (PHP_OS == 'Linux') {
            return 'Linux_x64';
        }

        if (PHP_OS == 'Darwin') {
            return 'Mac';
        }

        if (PHP_OS == 'FreeBSD') {
            return 'FreeBSD';
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
     * @return null|string
     * @throws \Beganovich\Snappdf\Exception\PlatformNotSupported
     */
    public function generatePlatformExecutable(string $revision, int $level = 3): ?string
    {
        $platform = $this->generatePlatformCode();

        if ($platform == 'Linux_x64') {
            return dirname(__FILE__, $level) . "/versions/{$revision}/chrome-linux/chrome";
        }

        if ($platform == 'FreeBSD') {
            return PHP_BINDIR . '/chrome';
        }

        if ($platform == 'Mac') {
            return dirname(__FILE__, $level) . "/versions/{$revision}/chrome-mac/Chromium.app";
        }

        if ($platform == 'Win' || $platform == 'Win_x64') {
            return dirname(__FILE__, $level) . "/versions/{$revision}/chrome-win/chrome.exe";
        }

        return null;
    }

    public function setCrashpadExecutable(string $revision, int $level = 3): void
    {
        $platform = $this->generatePlatformCode();

        if ($platform == 'Linux_x64') {
            $path = dirname(__FILE__, $level) . "/versions/{$revision}/chrome-linux/chrome_crashpad_handler";

            if (file_exists($path)) {
                chmod($path, 0755);
            }
        }
    }
}
