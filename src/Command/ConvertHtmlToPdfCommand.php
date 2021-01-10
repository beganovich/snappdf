<?php


namespace Beganovich\Snappdf\Command;

use Beganovich\Snappdf\Snappdf;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConvertHtmlToPdfCommand extends Command
{
    /**
     * Command signature.
     *
     * @var string
     */
    protected static $defaultName = 'convert';

    /**
     * Configure command propeprties.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Convert HTML to PDF')
            ->addOption('url', '', InputArgument::OPTIONAL)
            ->addOption('html', '', InputArgument::OPTIONAL)
            ->addOption('binary', null, InputArgument::OPTIONAL)
            ->addArgument('path', InputArgument::REQUIRED);
    }

    /**
     * The main command execute method.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Beganovich\Snappdf\Exception\BinaryNotFound
     * @throws \Beganovich\Snappdf\Exception\MissingContent
     * @throws \Beganovich\Snappdf\Exception\PlatformNotSupported
     * @throws \Beganovich\Snappdf\Exception\ProcessFailedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url = $input->getOption('url');
        $html = $input->getOption('html');
        $path = $input->getArgument('path');
        $binary = $input->getOption('binary');

        if (!$url && !$html) {
            $output->writeln('[ERROR] You must specify either --url or --html');

            return Command::FAILURE;
        }

        $snappdf = new Snappdf();

        if ($url) {
            $output->writeln(sprintf('Downloading %s and saving it to %s', $url, $path));

            $snappdf->setUrl($url);
        } elseif ($html) {
            $snappdf->setHtml($html);
        }

        if ($binary) {
            $output->writeln('Custom binary set. Using ' . $binary);

            $snappdf->setChromiumPath($binary);
        }

        $snappdf->save($path);

        $output->writeln('Success! PDF saved at ' . $path);

        return Command::SUCCESS;
    }
}
