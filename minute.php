<?php

class Queue
{
    protected $queue;
    protected $avgWait;
    protected $total;

    protected $filename;

    const MODIFIER = 3;

    public function __construct($filename)
    {
        $this->queue   = array();
        $this->avgWait = 0;
        $this->total   = 0;

        $this->filename = $filename;
    }

    public function load()
    {
        if (is_readable($this->filename) && filesize($this->filename) > 0) {
            $data = file($this->filename, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);

            $head = explode(' ', array_shift($data));

            if (count($head) != 2) {
                throw new UnexpectedValueException('Queue data file heading did not match expected format!');
            }

            $this->total   = $head[0];
            $this->avgWait = $head[1];
            $this->queue   = $data;
        }
    }

    public function save()
    {
        $handle = fopen($this->filename, 'w');

        fwrite($handle, "$this->total $this->avgWait\n");

        foreach($this->queue as $line) {
            fwrite($handle, "$line\n");
        }

        fclose($handle);
    }

    public function remove($time)
    {
        if (empty($this->queue)) {
            return;
        }

        $item = array_shift($this->queue);

        $this->total++;

        $this->avgWait -=  $this->avgWait / $this->total;
        $this->avgWait += ($time - $item) / $this->total;
    }

    public function add($time)
    {
        $this->queue[] = $time;
    }

    public function tick($time)
    {
        $queueAdd = mt_rand(1, static::MODIFIER * cos(deg2rad($time % 360)) + static::MODIFIER + 1);
        $queueRem = mt_rand(1, static::MODIFIER * sin(deg2rad($time % 360)) + static::MODIFIER + 1);

        for ($i=0; $i < $queueRem; $i++) {
            $this->remove($time);
        }

        for ($i=0; $i < $queueAdd; $i++) {
            $this->add($time);
        }

        return $queueAdd - $queueRem;
    }

    public function getAverage()
    {
        return $this->avgWait;
    }

    public function getMax()
    {
        if (empty($this->queue)) {
            return 0;
        }

        return microtime(true) - $this->queue[0];
    }

    public function getCount()
    {
        return count($this->queue);
    }

    public function getTotal()
    {
        return $this->total;
    }
}

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
