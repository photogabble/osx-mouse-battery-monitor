<?php

namespace MouseBattery;

use MouseBattery\Commands\Analyse;
use MouseBattery\Commands\Collect;
use Symfony\Component\Console\Application;

class MouseBattery extends Application
{

    public function __construct()
    {
        parent::__construct('Mouse Battery Monitor', '1.0.0');
        $this->addCommands([
            new Collect,
            new Analyse
        ]);
    }

}
