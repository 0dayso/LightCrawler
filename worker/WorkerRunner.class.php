<?php

/**
 * Worker Runner
 * User: Liang Tao (liangtaohy@163.com)
 * Date: 17/4/18
 * Time: PM7:03
 */

/**
 * -t target worker class
 * -h help
 */

$ext = ".class.php";

$params = getopt('t:hd');

$base = dirname(__FILE__);

$targetClass = isset($params['t']) && !empty($params['t']) ? $params['t'] : '';

$debug = isset($params['d']) ? true : false;

if (empty($targetClass)) {
    echo "Example: " . PHP_EOL;
    echo "-t DemoTargetWorker" . PHP_EOL;
    echo "please input a valid worker class name to run!!" . PHP_EOL;
    exit(0);
}

$target_file = $base . "/" . $targetClass . $ext;

if (!file_exists($target_file)) {
    echo "{$target_file} not existed!" . PHP_EOL;
    echo "Example: " . PHP_EOL;
    echo "-t DemoTargetWorker" . PHP_EOL;
    echo "please input a valid worker class name to run!!" . PHP_EOL;
    exit(0);
}

require_once $target_file;

if (!defined("CRAWLER_NAME")) {
    echo "CRAWLER_NAME should be defined in class {$targetClass}!" . PHP_EOL;
    exit(0);
}

$pid = posix_getpid();

file_put_contents('spider_' . $targetClass::MAGIC . '.pid', $pid);
gsettings()->debug = $debug;
if (gsettings()->debug == true) {
    gsettings()->url_cache_type = URL_CACHE_IN_MEMORY;
    gsettings()->enable_resume = false;
    gsettings()->number_of_process = 1;
}

$spider = new $targetClass();

echo "starting url: " . $targetClass::$SeedConf[0] . PHP_EOL;
$spider->setFeed($targetClass::$SeedConf[0]);

for ($i=1;$i<count($targetClass::$SeedConf); $i++) {
    echo "starting urls: " . $targetClass::$SeedConf[$i] . PHP_EOL;
    $spider->addStartingUrls($targetClass::$SeedConf[$i]);
}

$spider->run();