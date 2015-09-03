<?php

require_once "vendor/autoload.php";


$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->exclude('Tests')
    ->in(__DIR__)
;
$sf = new Symfony\CS\Config\Symfony23Config();

return $sf
    ->finder($finder)
    ;