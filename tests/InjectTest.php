<?php

use Envase\Inject;
use Envase\Test\Foo;
use Envase\Container;
use Envase\Test\FooWithAttr;
use Envase\NotFoundException;
use Envase\Test\FooDependency;
use Envase\Test\FooWithKeyAttr;
use Envase\Test\FooWithObjAttr;

it("can create inject object", function() {
    $ibject = new Inject("foo");
    expect($ibject->getKey())->toEqual("foo");
});

it('can inject property by property name', function () {
    $container= new Container(['foo' => 'bar']);
    $fooWAttr = $container->get(FooWithAttr::class);
    expect($fooWAttr)->toBeInstanceOf(FooWithAttr::class);
    expect($fooWAttr->getFoo())->toEqual("bar");
});

it('can inject property by key', function () {
    $container= new Container(['fooish' => 'bar']);
    $fooWAttr = $container->get(FooWithKeyAttr::class);
    expect($fooWAttr)->toBeInstanceOf(FooWithKeyAttr::class);
    expect($fooWAttr->getFoo())->toEqual("bar");
});

it('can create object from inject attribute', function () {
    $container= new Container;
    $container->set('fooSet', 'barSet');
    $foo = $container->get(FooWithObjAttr::class);
    expect($foo->getFoo())->toBeInstanceOf(FooDependency::class);
    expect($foo->getFoo()->foo)->toBeInstanceOf(Foo::class);
    expect($foo->getFoo()->fooSet)->toEqual('barSet');
});

it('throws TypeError on invalid type', function () {
    $container= new Container;
    $container->set('foo', new stdClass);
    $container->get(FooWithAttr::class);
})->throws(TypeError::class);

it('throws not found when cant find attribute dependency', function () {
    $container= new Container;
    $container->get(FooWithAttr::class);
})->throws(NotFoundException::class);

it('throws not found when cant find attribute key dependency', function () {
    $container= new Container;
    $container->get(FooWithKeyAttr::class);
})->throws(NotFoundException::class);

