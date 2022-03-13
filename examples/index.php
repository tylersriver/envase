<?php

use Envase\Container;
use Envase\Test\FooDependency;

require_once __DIR__ . '/../vendor/autoload.php';

$c = new Container([
    'foo' => 'bar',
    'fooArr' => ['bar', 'bar2']
]);
$c->set('fooSet', 'fooSet');

$obj = $c->get(FooDependency::class);

echo "Example";
exit;