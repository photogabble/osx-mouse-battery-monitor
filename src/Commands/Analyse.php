<?php

namespace MouseBattery\Commands;


use Exception;
use MouseBattery\Segment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Analyse extends Command
{

    /**
     * @var string Command Name
     */
    protected static $defaultName = 'analyse';

    private $segmentPointer = 0;

    /**
     * @var array|Segment[]
     */
    private $segments = [];

    protected function configure()
    {
        $this
            ->addOption('input_path', 'i', InputOption::VALUE_OPTIONAL, 'Path you want to output csv lines to.')
            ->addOption('progress', 'p', InputOption::VALUE_NONE, 'Display progress bar')
            ->addOption('BD_ADDR', 'a', InputOption::VALUE_REQUIRED, 'Bluetooth address for mouse you want to analyse')
            ->setDescription('Analyses input path and returns statistics.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $BD_ADDR = $input->getOption('BD_ADDR');
        $inputPath = $input->getOption('input_path');

        if (is_null($BD_ADDR)) {
            $output->writeln('<error>[!]</error> Please provide an address for the bluetooth device you want monitored.');
            return 1;
        }

        if (! file_exists($inputPath)) {
            $output->writeln(sprintf('<error>[!]</error> the file [%s] could not be read.', $inputPath));
            return 1;
        }

        // Generator
        $fileData = function(string $path) {
            $file = fopen($path, 'r');

            if (!$file)
                die('file does not exist or cannot be opened');

            while (($line = fgets($file)) !== false) {
                yield $line;
            }

            fclose($file);
        };

        $total = 0;
        $ignored = 0;

        foreach ($fileData($inputPath) as $line) {
            $line = explode(',', str_replace(array("\r", "\n"), '', $line));
            if (count($line) !== 3) { $ignored++; continue; }
            $line = array_combine(['ts', 'BD_ADDR', 'percentage'], array_map(function($v){return str_replace('"', '', $v);}, $line));
            $line['ts'] = (int) $line['ts'];

            if (count($this->segments) === 0) {
                $this->addSegment($line);
            } else if (! $this->segments[$this->segmentPointer-1]->add($line['ts'], $line['percentage'])){
                $this->addSegment($line);
                $this->segments[$this->segmentPointer-1]->add($line['ts'], $line['percentage']);
            }

            $total++;
        }

        // We should segment the data into 15 minute blocks and work out min/max/mean percentage
        // in order to generate a candle-stick plot.
        // The segment size should be adjustable.
        // Looks like there is some jitter with percentage going 100 -> 97 -> 99 -> 99 -> 98 -> ...

        $output->writeln(sprintf('Total Data Points: [<comment>%s</comment>], in [<comment>%s</comment>] segments.', number_format($total), number_format(count($this->segments))));

        return 0;
    }

    protected function addSegment(array $line) {
        $this->segments[$this->segmentPointer] = new Segment($line['ts']);
        $this->segmentPointer++;
    }
}
