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

    /**
     * @return string
     */
    public function getHtml(): ?string
    {
        return $this->html;
    }

    /**
     * @param string $html
     * @return ChromiumPdf
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
            throw new \Exception('No source provided. Make sure you call setHtml() or setUrl() before generate().');
        }

        $pdf = tempnam(sys_get_temp_dir(), 'pdf_');
        rename($pdf, $pdf .= '.pdf');

        $command = sprintf(
            '%s --headless --disable-gpu --print-to-pdf="%s" --print-to-pdf-no-header --hide-scrollbars --no-margins %s > /dev/null 2>&1',
            $this->getChromiumPath(), $pdf, $content['content']
        );

        exec($command, $output, $resultCode);

        return file_get_contents($pdf);
    }
}
