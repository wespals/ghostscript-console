<?php

namespace Test\GhostscriptConsole;

use Exception;
use GhostscriptConsole\GhostscriptCommand;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @coversDefaultClass GhostscriptCommand
 */
class GhostscriptCommandTest extends TestCase
{

    /**
     * @covers ::nothing
     * @return CommandTester
     */
    private function getCommandTester(): CommandTester
    {
        $application = new Application();
        $application->add(new GhostscriptCommand());

        return new CommandTester($application->find('ghostscript'));
    }

    /**
     * Get the PDF version from a PDF file
     *
     * @param string $pdfFile The full name and path of the PDF file
     * @return float
     * @throws InvalidArgumentException
     */
    private function getVersion(string $pdfFile): float
    {
        if ($handle = fopen($pdfFile, "rb")) {
            $line_first = fgets($handle);
            fclose($handle);
            preg_match_all('!\d+!', $line_first, $matches);

            return (float)implode('.', $matches[0]);
        }

        throw new InvalidArgumentException(sprintf("Unable to open PDF file: %s", $pdfFile));
    }

    /**
     * @covers ::execute
     * @throws Exception
     */
    public function testExecute(): void
    {
        $inputFile = __DIR__ . '/../../tests/files/2019FormW2-PDFv1.7.pdf';
        $outputFile = __DIR__ . '/../../tests/files/temp/FormW2.pdf';

        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            'inputFile' => $inputFile,
            'outputFile' => $outputFile
        ]);

        $this->assertEquals(1.7, $this->getVersion($inputFile));
        $output = trim($commandTester->getDisplay());
        $this->assertEquals(sprintf("File written: %s", $outputFile), $output);
        $this->assertFileExists($outputFile);
        $this->assertEquals(1.7, $this->getVersion($outputFile));
        unlink($outputFile);
    }

    /**
     * @covers ::execute
     * @throws Exception
     */
    public function testExecuteCompatibilityLevelOption(): void
    {
        $inputFile = __DIR__ . '/../../tests/files/2019FormW2-PDFv1.7.pdf';
        $outputFile = __DIR__ . '/../../tests/files/temp/FormW2.pdf';

        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            'inputFile' => $inputFile,
            'outputFile' => $outputFile,
            '--gs' => ['dCompatibilityLevel=1.4']
        ]);

        $this->assertEquals(1.7, $this->getVersion($inputFile));
        $output = trim($commandTester->getDisplay());
        $this->assertEquals(sprintf("File written: %s", $outputFile), $output);
        $this->assertFileExists($outputFile);
        $this->assertEquals(1.4, $this->getVersion($outputFile));
        unlink($outputFile);
    }

    /**
     * @covers ::execute
     */
    public function testExecuteBadOptionException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unknown device: invalidDevice');
        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            'inputFile' => __DIR__ . '/../../tests/files/2019FormW2-PDFv1.7.pdf',
            'outputFile' => __DIR__ . '/../../tests/files/temp/FormW2.pdf',
            '--gs' => ['sDEVICE=invalidDevice']
        ]);
    }

    /**
     * @covers ::execute
     */
    public function testExecuteEqualFilenameException(): void
    {
        $inputFile = __DIR__ . '/../../tests/files/2019FormW2-PDFv1.7.pdf';
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The input file and output file can not have the same value");
        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            'inputFile' => $inputFile,
            'outputFile' => $inputFile
        ]);
    }

    /**
     * @covers ::execute
     */
    public function testExecuteBlankInputFilenameException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The input file is required");
        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            'inputFile' => '',
            'outputFile' => __DIR__ . '/../../tests/files/temp/FormW2.pdf'
        ]);
    }

    /**
     * @covers ::execute
     */
    public function testExecuteNullInputFilenameException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("The input file is required");
        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            'inputFile' => null,
            'outputFile' => __DIR__ . '/../../tests/files/temp/FormW2.pdf'
        ]);
    }

    /**
     * @covers ::execute
     */
    public function testExecuteInputFileDoesNotExistException(): void
    {
        $inputFile = __DIR__ . '/../../tests/files/no-file.pdf';
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(sprintf(
            "The input file `%s` does not exist",
            $inputFile
        ));
        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            'inputFile' => $inputFile,
            'outputFile' => __DIR__ . '/../../tests/files/temp/FormW2.pdf'
        ]);
    }

    /**
     * @covers ::execute
     */
    public function testExecuteInputFileIsNotFileException(): void
    {
        $inputFile = __DIR__ . '/../../tests/files';
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(sprintf(
            "The input file `%s` is not a file",
            $inputFile
        ));
        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            'inputFile' => $inputFile,
            'outputFile' => __DIR__ . '/../../tests/files/temp/FormW2.pdf'
        ]);
    }

    /**
     * @covers ::execute
     */
    public function testExecuteOutputDirectoryDoesNotExistException(): void
    {
        $outputFile = __DIR__ . '/../../tests/files/no-dir/FormW2.pdf';
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(sprintf(
            "The output directory `%s` does not exist",
            dirname($outputFile)
        ));
        $commandTester = $this->getCommandTester();
        $commandTester->execute([
            'inputFile' => __DIR__ . '/../../tests/files/2019FormW2-PDFv1.7.pdf',
            'outputFile' => $outputFile
        ]);
    }

    /**
     * @covers ::nothing
     */
    public static function tearDownAfterClass(): void
    {
        unlink(__DIR__ . '/../../tests/files/temp/FormW2.pdf');
    }
}
