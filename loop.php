<?php

require_once 'queue.class.php';

$scriptName = array_shift($argv);

$usage = <<<USAGE
Queue Simulator

  Usage:
    php $scriptName [options]

  Options:
    -h | --help       : Display this help text
    -i | --iterations : Limit queue simulation to this number of iterations
                        Default: unlimited
    -f | --file       : Use this file to load/save queue data
                        Default: queue.dat
    -d | --delay      : Pause this many seconds between iterations. Passing 0
                        causes the queue to run in pure simulation mode,
                        emulating one second delay between iterations without
                        actually pausing.
                        Default: 5
    -c | --csv        : Output status information in CSV format

  Description:
    Queue Simulator emulates a customer qeueue. On each iteration a random
    number of customers between 1 and 7 will be added to the queue, and some
    number will be removed. The queue uses sine and cosine functions to
    replicate a natural rise and fall in customer demand and response. Queue
    data is cached in a file and will be reloaded at the beginning of execution
    (if it exists). If no iteration limit is given, queue data is saved on each
    iteration, otherwise data is saved at the end of the simulation.


USAGE;

// Default values
$filename  = 'queue.dat';
$delay     = 5;
$limit     = 0;
$count     = 0;
$csvFormat = false;

// Parse commandline options
$options = getopt('hi:f:d:c', array('help', 'iterations:', 'file:', 'delay:', '--csv'));

foreach ($options as $key => $value) {
    switch ($key) {
        case 'h':
        case 'help':
            die($usage);
        case 'i':
        case 'iterations':
            if (!ctype_digit($value)) die($usage);
            $limit = $value;
            break;
        case 'f':
        case 'file':
            $filename = $value;
            break;
        case 'd':
        case 'delay':
            if (!ctype_digit($value)) die($usage);
            $delay = $value;
            break;
        case 'c':
        case 'csv':
            $csvFormat = true;
            break;
    }
}

if (!@touch($filename) || !is_writable($filename)) {
    die("Cannot write to \"$filename\"!");
}

// Start the queue!
$q = new Queue($filename);
$q->load();
$q->calculateSkew($delay);

if ($csvFormat) {
    echo '"Net Change","Max Wait","Average Wait",Length,"Total Processed"' . PHP_EOL;
} else {
    echo "Net Change | Max Wait | Average Wait | Length | Total Processed\n";
    echo "---------- + -------- + ------------ + ------ + ---------------\n";
}

$startTime = time();

// If limit is 0 just run forever
while ($limit === 0 || $count < $limit) {
    if ($delay == 0) {
        $change = $q->tick($startTime + $count);
    } else {
        $change = $q->tick();
    }

    if ($csvFormat) {
        $format = "%d,%01.4f,%01.4f,%d,%d\n";
    } else {
        // Default output format for net change
        $chFormat = '%+10d';

        // Don't output a sign value if net change is 0
        if ($change == 0) {
            $chFormat = '%10d';
        }

        $format = "$chFormat | %8d | %12.4f | %6d | %15d\n";
    }

    printf($format, $change, $q->getMax(), $q->getAverage(), $q->getCount(), $q->getTotal());

    // Save on each iteration if we're running forever
    if ($limit === 0) $q->save();

    $count++;
    sleep($delay);
}

// Save once loop is complete
$q->save();
