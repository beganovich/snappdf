<?php


namespace Beganovich\Snappdf\Command;

use Beganovich\Snappdf\Snappdf;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeTestPdfCommand extends Command
{
    /**
     * Command signature.
     *
     * @var string
     */
    protected static $defaultName = 'test';

    /**
     * Configure command propeprties.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Make a test PDF')
            ->addArgument('path', InputArgument::REQUIRED)
            ->addArgument('binary', InputArgument::OPTIONAL);
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
        $output->writeln('Generating a test PDF..');

        $snappdf = new Snappdf();

        if ($input->getArgument('binary')) {
            $snappdf = $snappdf->setChromiumPath($input->getArgument('binary'));

            $output->writeln('Custom binary set. Using ' . $input->getArgument('binary'));
        }

        $snappdf
            ->setHtml('<h1>It works!</h1>')
            ->save($input->getArgument('path'));

        $output->write('Success! PDF saved at ' . $input->getArgument('path'));

        return Command::SUCCESS;
    }
}
