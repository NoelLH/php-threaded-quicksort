<?php
require __DIR__ . '/vendor/autoload.php';

// 1 million elements in random order
$array = \Quicksort\Sort::getRandomisedArray(1000000);

$start = microtime(true);

$sorter = new \Quicksort\Sort($array);
$sorter->start();

echo 'Took ' . (microtime(true) - $start) . 's';
