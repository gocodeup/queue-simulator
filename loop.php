<?php

require_once 'queue.class.php';

define('QUEUE_FILE', 'queue.dat');

$q = new Queue(QUEUE_FILE);
$q->load();

while (true) {
    $change = $q->tick(time());

    $chFormat = '%+d';
    if ($change == 0) {
        $chFormat = ' %d';
    }

    $format = "Net Change: $chFormat | Max Wait: %07.4f | Average: %07.4f | Length: %3s | Total: %6s\n";

    printf($format, $change, $q->getMax(), $q->getAverage(), $q->getCount(), $q->getTotal());

    $q->save();
    sleep(1);
}
