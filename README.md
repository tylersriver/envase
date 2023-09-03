# Envase
[![Codecov](https://codecov.io/gh/tylersriver/envase/branch/main/graph/badge.svg?token=HE1M6KNO9G)](https://codecov.io/gh/tylersriver/envase) [![Build](https://github.com/tylersriver/envase/actions/workflows/php.yml/badge.svg)](https://codecov.io/gh/tylersriver/envase) [![License](https://img.shields.io/github/license/tylersriver/envase)](https://github.com/tylersriver/envase/blob/main/LICENSE) [![Version](https://img.shields.io/packagist/v/tyler/envase)](https://packagist.org/packages/tyler/envase) [![Downloads](https://img.shields.io/packagist/dt/tyler/envase)](https://packagist.org/packages/tyler/envase)

Very tiny PHP implementation of the [PSR-11 Container](https://www.php-fig.org/psr/psr-11/). 

"Envase" is Spanish for Container.

# About
Envase container is extremely easy to configure and use. It features a 
static registry as well as the ability to resolve classes and their dependencies using **autowiring**. Like a lot of container implementations
when you call `get` it will only resolve the instance the *first* time and any future call will return the original resolved instance.

# Installation
```cli
composer require tyler/envase
```
# Usage

## Basic
```php

// Static definitions
// simple key => val pairs
$defs = [
    'foo' => 'bar'
    'config' => [
        'dir' => '/path/to/something/'
    ],
    'Session' => new Session
];

// Create container with defs
$c = new Envase\Container($defs);

// Add additional defs
$c->set('new', 'value');

// retrieve item
$item = $c->get('foo');

// $item will equal 'bar'
```
## Closures
So we can lazy load class instances at the time of first use
you can add definitions as Closures that will be invoked the first time
`get` is called.

```php
$c->set('foo', fn(Envase\Container $c) => new Bar);

// or the short hand helper
$c->set('foo', Envase\get(Bar::class))

$bar = $c->get('foo'); // <-- will result in calling the closure
```
Note: The next time get is called for 'foo' the resolved instance will
be returned

## Autowiring
By default (might change later) if you ask the container for an object 
it will resolve all of the `__construct` dependencies recursively with
the container. IF a constructor argument is a primitive type it will attempt to resolve it by name from the container;
```php
class Foo 
{
    public function __construct(
        public Bar $bar,
        public string $dirToSomething
        
    )
}

class Bar
{
}

$c = new Envase\Container([
    'dirToSomething' => '/path/to/somewhere'
]);

$foo = $c->get(Foo::class);
```
In this case `$foo` will be an instance of class `Foo` and will have the
properties `$bar = instance of Bar` and `$dirToSomething = '/path/to/somewhere'`

## Injectable Properties
If a property on a class being resolved by the container has the `#[Inject]` attribute, the container
will use that to set that properties value. 

> If you set this property form the constructor, Injected properties will override the constructor set 

```php
class Foo 
{
    #[Inject]
    private Bar $bar; // will auto inject instance of Bar

    #[Inject]
    private string $foo // Will auto inject key foo, value will be 'bar'

    #[Inject("fizz")]
    private string $something // Will auto inject key fizz, value will be 'buzz'
}

class Bar
{
}

$c = new Envase\Container([
    'foo' => 'bar',
    'fizz' => 'buzz'
]);

$foo = $c->get(Foo::class); // will be instance of Foo
```
If a key or object can't be resovled the container will throw a `NotFoundException`

## Making objects
IF you need to make a new instance of a class you can call the containers
`make` method. Note, this will only make a new instance of that class, all
dependencies will be resolved from the container;
```php
$foo = $c->make(Foo::class);
```