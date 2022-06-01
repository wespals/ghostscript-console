<?php

namespace GhostscriptConsole;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @see https://www.ghostscript.com/documentation/index.html Ghostscript Documentation
 */
class GhostscriptCommand extends Command
{

    protected function configure(): void
    {
        $this->setName('ghostscript')
            ->setDescription('A PHP CLI wrapper to the Ghostscript executable')
            ->setHelp(<<<EOD
This command offers a PHP CLI wrapper to the Ghostscript executable.
The following parameters are automatically set if not explicitly included in the command.
    <info>-dSAFER -dBATCH -dNOPAUSE -sDEVICE=pdfwrite</info>
More about the Ghostscript CLI options can be found at
    <info>https://www.ghostscript.com/doc/current/Use.htm#Options</info>
The following is an example of using the <info>%command.name%</info>
    <info>php %command.full_name% -g 'sDEVICE=pdfwrite' --gs='dCompatibilityLevel=1.4' /full/input/path/pdf1.pdf /full/output/path/pdf2.pdf</info>
EOD
            )
            ->addArgument(
                'inputFile',
                InputArgument::REQUIRED,
                'The full path filename of the source PDF file'
            )
            ->addArgument(
                'outputFile',
                InputArgument::REQUIRED,
                'The full path filename of the PDF output file'
            )
            ->addOption(
                'gs',
                'g',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Any valid Ghostscript parameters'
            );
    }

    /**
     * Verifies Ghostscript is installed on the system
     *
     * @return bool
     */
    private function isGhostscriptInstalled(): bool
    {
        $cmd = (strpos(PHP_OS, 'WIN') === 0) ? 'where' : 'command -v';

        return is_executable(trim(shell_exec("$cmd gs")));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->isGhostscriptInstalled()) {
            throw new RuntimeException('Ghostscript is not installed');
        }

        if ($input->getArgument('inputFile') === $input->getArgument('outputFile')) {
            throw new InvalidArgumentException("The input file and output file can not have the same value");
        }

        if ($input->getArgument('inputFile') === null || $input->getArgument('inputFile') === '') {
            throw new InvalidArgumentException("The input file is required");
        }

        if (!file_exists($input->getArgument('inputFile'))) {
            throw new InvalidArgumentException(sprintf(
                "The input file `%s` does not exist",
                $input->getArgument('inputFile')
            ));
        }

        if (!is_file($input->getArgument('inputFile'))) {
            throw new InvalidArgumentException(sprintf(
                "The input file `%s` is not a file",
                $input->getArgument('inputFile')
            ));
        }

        if (!file_exists(dirname($input->getArgument('outputFile')))) {
            throw new InvalidArgumentException(sprintf(
                "The output directory `%s` does not exist",
                dirname($input->getArgument('outputFile'))
            ));
        }

        // set some default values
        $defaults = [];
        if (empty(preg_grep("/^dSAFER/", $input->getOption('gs')))) {
            $defaults[] = "dSAFER";
        }

        if (empty(preg_grep("/^dBATCH$/", $input->getOption('gs')))) {
            $defaults[] = "dBATCH";
        }

        if (empty(preg_grep("/^dNOPAUSE/", $input->getOption('gs')))) {
            $defaults[] = "dNOPAUSE";
        }

        if (empty(preg_grep("/^sDEVICE/", $input->getOption('gs')))) {
            $defaults[] = "sDEVICE=pdfwrite";
        }

        $input->setOption('gs', array_merge($defaults, array_values($input->getOption('gs'))));

        exec(sprintf(
            "gs -%s -sOutputFile='%s' '%s'",
            implode(" -", $input->getOption('gs')),
            $input->getArgument('outputFile'),
            $input->getArgument('inputFile')
        ), $returnArray, $returnValue);

        if (0 !== $returnValue) {
            throw new RuntimeException(end($returnArray));
        }

        $output->writeln(sprintf(
            "<info>File written: %s</info>",
            $input->getArgument('outputFile')
        ));
    }
}
