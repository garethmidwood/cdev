<?php
namespace Creode\System\Aws;

use Creode\System\Command;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Aws extends Command
{
    public function __construct(OutputInterface $output)
    {
        if (!`which aws`) {
            $output->writeln('Error: <fg=yellow>AWS cli must be installed to use this function</fg=yellow>');
            exit;
        }
    }

}
