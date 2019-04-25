<?php

namespace MouseBattery\Commands;

use CFPropertyList\CFPropertyList;
use CFPropertyList\IOException;
use DOMException;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Collect extends Command
{

    /**
     * @var string Command Name
     */
    protected static $defaultName = 'collect';

    protected function configure()
    {
        $this
            ->addOption('output_path', 'o', InputOption::VALUE_OPTIONAL, 'Path you want to output csv lines to.')
            ->addOption('progress', 'p', InputOption::VALUE_NONE, 'Display progress bar')
            ->addOption('BD_ADDR', 'a', InputOption::VALUE_REQUIRED, 'Bluetooth address for mouse you want to monitor')
            ->setDescription('Parses input from stdin');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws IOException
     * @throws DOMException
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $BD_ADDR = $input->getOption('BD_ADDR');

        if (is_null($BD_ADDR)) {
            $output->writeln('<error>[!]</error> Please provide an address for the bluetooth device you want monitored.');
            return 1;
        }

        if (! $fp = fopen("php://stdin", "r")) {
            throw new Exception('stdin could not be opened for reading');
        }

        // @todo need to identify when stdin is empty and exit with error 1

        $plist = new CFPropertyList();
        $plist->loadXMLStream($fp);
        fclose($fp);

        $arr = $plist->toArray();
        $devices = $arr[0]['_items'][0]['device_title'][0]; //[0]['device_title'][0];
        $found = null;

        foreach (array_keys($devices) as $name) {

            if ($devices[$name]['device_addr'] === $BD_ADDR) {
                if ($output->isVeryVerbose()) {
                    $output->writeln(sprintf('Bluetooth device [<comment>%s</comment>] found matching BD_ADDR [<comment>%s</comment>]', $name, $BD_ADDR));
                }
                $found = $devices[$name];
                break;
            }
        }

        if (is_null($found)) {
            $output->writeln(sprintf('<error>[!]</error> Could not find a bluetooth device with BD_ADDR [<comment>%s</comment>]', $BD_ADDR));
            return 2;
        }

        $batteryPercentage = (int) str_replace('%', '', $found['device_batteryPercent']);
        $timestamp = 0;

        if ($path = $input->getOption('output_path')) {
            $this->writeToFile($batteryPercentage, $BD_ADDR ,$path);
            $timestamp = $this->getFirstTimestamp($path);
        }

        if ($input->getOption('progress')) {
            $this->displayProgress($batteryPercentage, $timestamp, $output);
        }

        return 0;
    }

    /**
     * Returns a timestamp for the first recorded sample.
     *
     * @param string $filename
     * @return int
     */
    private function getFirstTimestamp(string $filename): int {
        if (! file_exists($filename)) {
            return 0;
        }

        // PHP implements RAII for resources so no need to fclose
        $line = fgets(fopen($filename, 'r'));
        $arr = explode(',', $line);

        if (count($arr) !== 3) {
            return 0;
        }

        return (int) str_replace('"', '', $arr[0]);
    }

    /**
     * Writes current sample to disk.
     *
     * @param int $batteryPercentage
     * @param string $id
     * @param string $filename
     */
    private function writeToFile(int $batteryPercentage, string $id, string $filename) {
        if (! file_exists($filename)) {
            touch($filename);
        }

        file_put_contents($filename, sprintf('"%s","%s","%s"'. PHP_EOL, time(), $id, str_replace('%', '', $batteryPercentage)), FILE_APPEND);
    }

    /**
     * Display pretty output.
     *
     * @todo add eta?
     * @param int $batteryPercentage
     * @param int $firstTimestamp
     * @param OutputInterface $output
     */
    private function displayProgress (int $batteryPercentage, int $firstTimestamp, OutputInterface $output) {
        $progressBar = new ProgressBar($output, 100);
        $message = '';

        if ('\\' !== \DIRECTORY_SEPARATOR || 'Hyper' === getenv('TERM_PROGRAM')) {
            $progressBar->setEmptyBarCharacter('░'); // light shade character \u2591
            $progressBar->setProgressCharacter('');
            $progressBar->setBarCharacter('▓'); // dark shade character \u2593
        }

        if ($batteryPercentage <= 12) {
            $format = time() . ' <comment>%percent:3s%%</comment> [%bar%] %message%';
        } else {
            $format = '<info>%percent:3s%%</info> [%bar%] %message%';
        }

        if ($firstTimestamp > 0) {
            $message = $this->dateDifference(time(), $firstTimestamp);
        }

        $progressBar->setMessage($message);
        $progressBar->setFormat($format);
        $progressBar->setProgress($batteryPercentage);
        $progressBar->display();
        $output->writeln('');
    }

    private function dateDifference(int $from , int $to , $differenceFormat = '%d Days %h Hours' ): string
    {
        if (! $datetime1 = date_create("@$from")) {
            return '';
        }
        if (! $datetime2 = date_create("@$to")) {
            return '';
        }

        $interval = date_diff($datetime1, $datetime2);
        return $interval->format($differenceFormat);
    }
}
