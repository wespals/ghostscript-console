<?php

use GhostscriptConsole\{GhostscriptCommand};
use Symfony\Component\Console\Application;

$files = [
    __DIR__ . '/../../../autoload.php', // composer dependency
    __DIR__ . '/../vendor/autoload.php', // stand-alone package
];
$loader = null;

foreach ($files as $file) {
    if (file_exists($file)) {
        $loader = require $file;

        break;
    }
}

if (!$loader) {
    throw new RuntimeException('vendor/autoload.php could not be found. Did you run `php composer.phar install`?');
}

if (!class_exists(Application::class)) {
    throw new RuntimeException('You need to add "symfony/console" as a Composer dependency.');
}

$console = new Application();
$console->addCommands([
    new GhostscriptCommand()
]);
$console->run();