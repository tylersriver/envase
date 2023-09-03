<?php

use Envase\Container;
use Envase\NotFoundException;
use Envase\Test\Foo;
use Envase\Test\FooDependency;
use Envase\Test\FooImplementation;
use Envase\Test\FooInterface;

it("throws NotFoundException", function () {
    $c = new Container;
    $c->get('foo');
})->throws(NotFoundException::class, 'Key not found');

it('finds foo and it equals bar', function() {
    $c = new Container(['foo' => 'bar']);
    $val = $c->get('foo');
    expect($val)->toBe('bar');
});

it('resolves closure and second find', function() {
    $c = new Container(['foo' => fn($c) => 'bar']);
    $val = $c->get('foo');
    expect($val)->toBe('bar');

    $val = $c->Get('foo');
    expect($val)->toBe('bar');
});

it('resolves to Foo class instance', function() {
    $c = new Container;
    $obj = $c->get(Foo::class);

    expect($obj)->toBeInstanceOf(Foo::class);
});

it('resolves object dependencies', function() {
    $c = new Container;
    $c->set('fooSet', 'barSet');
    
    /** @var $obj FooDependency */
    $obj = $c->get(FooDependency::class);

    expect($obj)->toBeInstanceOf(FooDependency::class);
    expect($obj->foo)->toBeInstanceOf(Foo::class);
    expect($obj->fooSet)->toBe('barSet');
});

it('can take multiple definitions sources', function() {
    $c = new Container(['foo' => 'bar']);
    $c->add(['foo' => 'jar']);

    expect($c->get('foo'))->toBe('jar');

    $c->add(['fizz' => 'buzz']);
    expect($c->has('foo'))->toBeTrue();
    expect($c->has('fizz'))->toBeTrue();
    expect($c->get('fizz'))->toBe('buzz');

});

it('resolves definitions from file', function() {
    $file = __DIR__ . '/definitions.php';
    $c = new Container($file);
    $c->add(['test' => 'yes']);
    $c->set('this', 'that');

    expect($c->has('foo'))->toBeTrue();
    expect($c->has('fizz'))->toBeTrue();
    expect($c->get('fizz'))->toBe('buzz');
    expect($c->get('foo'))->toBe('bar');
    expect($c->get('test'))->toBe('yes');
    expect($c->get('this'))->toBe('that');
});

it('errors for no interface mapped', function () {
    $c = new Container;
    $c->get(FooInterface::class);
})->throws(NotFoundException::class);

it('resolves mapped interface', function () {
    $c = new Container([
        FooInterface::class => fn($c) => $c->get(FooImplementation::class)
    ]);
    $obj = $c->get(FooInterface::class);
    expect($obj)->toBeInstanceOf(FooImplementation::class);
});

