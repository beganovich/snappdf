<?php


namespace Beganovich\Snappdf\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class UpdateActiveBinary extends Command
{
    protected static $defaultName = 'use';

    protected function configure()
    {
        $this
            ->setDescription('Use a specific version of Chromium revision.')
            ->addArgument('revision', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystem = new Filesystem();

        $output->writeln("versions/{$input->getArgument('revision')}");

        $filesystem->symlink(
            dirname(__FILE__, 3) . "/versions/{$input->getArgument('revision')}/chrome-linux/chrome", 
            dirname(__FILE__, 3) . '/versions/chrome'
        );

        chmod(dirname(__FILE__, 3) . '/versions/chrome', 0755);

        $filesystem->remove(dirname(__FILE__, 3) . "/versions/{$input->getArgument('revision')}.zip");

        return Command::SUCCESS;
    }
}
