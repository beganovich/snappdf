<?php

namespace Beganovich\Snappdf;

use Beganovich\Snappdf\Exception\BinaryNotFound;
use Beganovich\Snappdf\Exception\MissingContent;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Snappdf
{
    /**
     * @var string
     */
    private $chromiumPath;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $html;

    public function __construct()
    {
        // ..
    }

    /**
     * @return string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return Snappdf
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     * @throws BinaryNotFound
     */
    public function getChromiumPath(): string
    {
        if ($this->chromiumPath) {
            return $this->chromiumPath;
        }

        $builtInChromium = dirname(__FILE__, 2) . '/versions/chrome';

        if (file_exists($builtInChromium) && is_executable($builtInChromium)) {
            return $builtInChromium;
        }

        throw new BinaryNotFound('Browser binary not found. Make sure you download it or set using setChromiumPath().');
    }

    /**
     * @param string $chromiumPath
     * @return Snappdf
     */
    public function setChromiumPath(string $chromiumPath): self
    {
        $this->chromiumPath = $chromiumPath;

        return $this;
    }

    /**
     * @return string
     */
    public function getHtml(): ?string
    {
        return $this->html;
    }

    /**
     * @param string $html
     * @return Snappdf
     */
    public function setHtml(string $html): self
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Main method to generate PDFs.
     *
     * @return string
     * @throws MissingContent
     */
    public function generate()
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

        $command = sprintf(
            '%s --headless --disable-gpu --print-to-pdf="%s" --print-to-pdf-no-header --hide-scrollbars --no-margins --no-sandbox %s',
            $this->getChromiumPath(),
            $pdf,
            $content['content']
        );

        $process = Process::fromShellCommandline($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return file_get_contents($pdf);
    }
}
