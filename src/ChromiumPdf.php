<?php

namespace Beganovich\ChromiumPdf;

class ChromiumPdf
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
    private $outputPath;

    public function __construct()
    {
        // ..
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return ChromiumPdf
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getOutputPath(): string
    {
        return $this->outputPath;
    }

    /**
     * @param string $outputPath
     * @return ChromiumPdf
     */
    public function setOutputPath(string $outputPath): self
    {
        $this->outputPath = $outputPath;

        return $this;
    }

    /**
     * @return string
     */
    public function getChromiumPath(): string
    {
        return $this->chromiumPath;
    }

    /**
     * @param string $chromiumPath
     * @return ChromiumPdf
     */
    public function setChromiumPath(string $chromiumPath): self
    {
        $this->chromiumPath = $chromiumPath;

        return $this;
    }

    public function generate()
    {
        $command = sprintf(
            '%s --headless --disable-gpu --print-to-pdf="%s" %s',
            $this->getChromiumPath(), $this->getOutputPath(), $this->getUrl()
        );

        exec($command);
    }
}
