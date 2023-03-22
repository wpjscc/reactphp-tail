<?php

require __DIR__ . '/vendor/autoload.php';

use React\EventLoop\Loop;

require './Montior.php';


$monitor = (new Montior);


$monitor->debug = getParam('--debug') ? true : false;
$callback = function ($buffer) {
    echo $buffer;
};

$monitor->run(
    getParam('--path'), 
    (array) getParam('--name'), 
    $callback
);


Loop::addPeriodicTimer(5, function () use ($monitor, $callback) {
    $memory = memory_get_usage() / 1024;
    $formatted = number_format($memory, 3).'K';
    $monitor->info("Current memory usage: {$formatted}");

    // var_dump($monitor->files);
    $monitor->run(
        getParam('--path'), 
        (array) getParam('--name'), 
        $callback
    );
});



function getParam($key, $default = null){
    $data = [];
    foreach ($GLOBALS['argv'] as $arg) {
        if (strpos($arg, $key) !==false){
            $data[] = explode('=', $arg)[1];
        }
    }

    if ($data) {
        return $data;
    }

    return $default;
}

