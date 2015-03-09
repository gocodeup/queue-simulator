<?php

require_once 'queue.class.php';

define('QUEUE_FILE', 'queue.dat');

$q = new Queue(QUEUE_FILE);
$q->load();
$q->calculateSkew(1);

$change = $q->tick();

$data = array(
    'net_change' => $change,
    'wait'       => array(
        'current_max' => $q->getMax(),
        'average'     => $q->getAverage()
    ),
    'length'       => $q->getCount(),
    'total_served' => $q->getTotal()
);

$q->save();

echo json_encode($data, JSON_NUMERIC_CHECK|JSON_PRETTY_PRINT) . PHP_EOL;
