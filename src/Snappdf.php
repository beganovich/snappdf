<?php

namespace Beganovich\Snappdf;

use Beganovich\Snappdf\Command\DownloadChromiumCommand;
use Beganovich\Snappdf\Exception\BinaryNotExecutable;
use Beganovich\Snappdf\Exception\BinaryNotFound;
use Beganovich\Snappdf\Exception\MissingContent;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class Snappdf
{
    private $chromiumPath;

    private $chromiumArguments = [];

    private $url;

    private $html;

    private $waitBeforePrinting;

    public function __construct()
    {
        $this->chromiumArguments = [
            '--headless',
            '--disable-gpu',
            '--disable-translate',
            '--disable-extensions',
            '--disable-sync',
            '--disable-background-networking',
            '--disable-software-rasterizer',
            '--disable-default-apps',
            '--disable-dev-shm-usage',
            '--safebrowsing-disable-auto-update',
            '--run-all-compositor-stages-before-draw',
            '--no-first-run',
            '--no-margins',
            '--no-sandbox',
            '--print-to-pdf-no-header',
            '--hide-scrollbars',
            '--ignore-certificate-errors',
        ];
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getChromiumPath(): string
    {
        if ($this->chromiumPath) {
            return $this->chromiumPath;
        }

        if (getenv('SNAPPDF_EXECUTABLE_PATH')) {
            return getenv('SNAPPDF_EXECUTABLE_PATH');
        }

        $latestRevisionFile = dirname(__FILE__, 2) . '/versions/revision.txt';

        // FreeBSD Support.  Chromium is typically installed via packages or ports.
        if (PHP_OS == 'FreeBSD' && file_exists(PHP_BINDIR . '/chrome')) {
            return PHP_BINDIR . '/chrome';
        }

        if (file_exists($latestRevisionFile)) {
            $chromuimBinary = (new DownloadChromiumCommand())->generatePlatformExecutable(
                file_get_contents($latestRevisionFile)
            );

            if (!is_executable($chromuimBinary)) {
                throw new BinaryNotExecutable('Downloaded Chromium binary is not executable. Make sure to set correct permissions (0755)');
            }

            return $chromuimBinary;
        }

        throw new BinaryNotFound('Browser binary not found. Make sure you download it or set using setChromiumPath().');
    }

    public function setChromiumPath(string $chromiumPath): self
    {
        $this->chromiumPath = $chromiumPath;

        return $this;
    }

    public function getChromiumArguments(): array
    {        
        if (getenv('SNAPPDF_EXECUTABLE_ARGUMENTS')) {
            $arguments = explode(" ", getenv('SNAPPDF_EXECUTABLE_ARGUMENTS'));
        } else {
            $arguments = $this->chromiumArguments;
        }

        if ($this->waitBeforePrinting) {
            $matches  = preg_grep ('/--virtual-time-budget=(.*)/', $arguments);
            if (count($matches) > 0) {
                $arguments = preg_replace(
                    '/--virtual-time-budget=(.*)/',
                    '--virtual-time-budget=' . (int)$this->waitBeforePrinting,
                    $arguments
                );
            }else{
                array_splice($arguments, 2, 0, ['--virtual-time-budget=' . (int)$this->waitBeforePrinting]);
            }
        }

        return $arguments;
    }

    public function addChromiumArguments(string $chromiumArgument): self
    {
        $arguments = explode(" ", $chromiumArgument);
        
        foreach ($arguments as $argument) {
            $arg = explode('=', $argument);
            $matches  = preg_grep ('/'.$arg[0].'(.*)/', $this->chromiumArguments);
            if (count($matches) > 0) {
                $this->chromiumArguments = preg_replace(
                    '/'.$arg[0].'(.*)/',
                    $argument,
                    $this->chromiumArguments
                );
            } else {
                $this->chromiumArguments = array_merge($this->chromiumArguments, [$argument]);
            }
        }
        return $this;
    }

    public function clearChromiumArguments(): self
    {
        $this->chromiumArguments = [];

        return $this;
    }

    public function getHtml(): ?string
    {
        return $this->html;
    }

    public function setHtml(string $html): self
    {
        $this->html = $html;

        return $this;
    }

    public function waitBeforePrinting(int $waitBeforePrinting): self
    {
        $this->waitBeforePrinting = $waitBeforePrinting;

        return $this;
    }

    public function getWaitTime(): ?int
    {
        return $this->waitBeforePrinting;
    }

    public function generate(): ?string
    {
        $content = [
            'type' => null,
            'content' => null,
        ];

        if ($this->getUrl()) {
            $content['type'] = 'url';
            $content['content'] = $this->getUrl();
        }

        if ($this->getHtml()) {
            $temporaryFile = tempnam(sys_get_temp_dir(), 'html_');
            rename($temporaryFile, $temporaryFile .= '.html');
            file_put_contents($temporaryFile, $this->getHtml());

            $content['type'] = 'html';
            $content['content'] = $temporaryFile;
        }

        if (!$content['content']) {
            throw new MissingContent('No content provided. Make sure you call setHtml() or setUrl() before generate().');
        }

        $pdf = tempnam(sys_get_temp_dir(), 'pdf_');
        rename($pdf, $pdf .= '.pdf');

        $commandInput = [ $this->getChromiumPath() ];
        foreach ($this->getChromiumArguments() as $argument) {
            array_push($commandInput, $argument);
        }

        array_push(
            $commandInput,
            '--print-to-pdf=' . $pdf,
            $content['content'],
        );

        $platform = (new DownloadChromiumCommand())->generatePlatformCode();

        if ($platform == 'Win' || $platform == 'Win_x64') {
            return $this->executeOnWindows($commandInput, $pdf);
        }

        $process = new Process($commandInput);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Symfony\Component\Process\Exception\ProcessFailedException($process);
        }

        return file_get_contents($pdf);
    }

    public function save(string $path): void
    {
        $pdf = $this->generate();

        $filesystem = new Filesystem();

        $filesystem->appendToFile($path, $pdf);
    }

    private function executeOnWindows(array $commands, $pdf): ?string
    {
        $command = implode(' ', $commands);

        exec($command, $output, $statusCode);

        if (!$statusCode) {
            throw new \Beganovich\Snappdf\Exception\ProcessFailedException($output);
        }

        return file_get_contents($pdf);
    }
}
