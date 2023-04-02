
Symfony Cartography bundle
========================
![tests](https://github.com/micoli/symfony-cartography/actions/workflows/lint.yaml/badge.svg)
![lint](https://github.com/micoli/symfony-cartography/actions/workflows/tests.yaml/badge.svg)

The aim of this bundle is to:
- provide an analysis of your project code bases (classes, class categorization, method calls to other service/class)
- draw a map of all possible method calls from and to a specific class::method. be ware that this map is calculated through static analysis, it's not the classes:methods called during a specific execution, it's all the possible calls from and to a class:method


![toolbar](docs/simple-map.png)

 
Requirements
------------

  * PHP 8.1.0 or higher;
  * PDO-SQLite PHP extension enabled;

Installation
------------
```bash
composer require --dev micoli/symfony-cartography
```
- add the line in `config\bundles.php`
`Micoli\SymfonyCartography\SymfonyCartographyBundle::class => ['dev' => true, 'test' => true],`

- and create `config/packages/symfony_cartography.yaml`

```
symfony_cartography:
  enabled: true # enable or disable the collection of request controllers identification and the refreshing of the analyzed codebase
  sources: # paths you want to be analyzed
    - '%kernel.project_dir%/src'
  filters:
    classes:
      rules: 
        - '+App\' # all classes in namespace 'App\' will be excluding
        - '-App\Domain\DataFixtures' # except thos starting by 'App\Domain\DataFixtures' 
    method_calls:
      exclude_loopback: true #display sameClasse to sameClasse arrows
      rules:
      - '-Twig\' # do not display call towards classes in 'twig' namespace
      - '-ReflectionFunctionAbstract'
      - '-Doctrine\'
      - '-Symfony\'
      - '-Webmozart\'
      - '-Psr\'
  messenger_dispatchers: # Class/Method used to wire event trough messenger buses
    - class: App\Infrastructure\Bus\MessengerCommandBus
      method: dispatch
    - class: App\Infrastructure\Bus\MessengerEventDispatcher
      method: dispatch
  graph:
    withMethodDisplay: false # display methods in classes
    withMethodArrows: false # if disabled, only one arrow from a class to another is draw
    leftToRightDirection: false # if disabled graph is drawn top to bottom, else it is draw left To Right 
  colors: # colors used in graph
      - class: !php/enum Micoli\SymfonyCartography\Service\Categorizer\ClassCategory::undefined
        color: '#033270'
      - class: !php/enum Micoli\SymfonyCartography\Service\Categorizer\ClassCategory::doctrineEntity
        color: '#1368aa'
      - class: !php/enum Micoli\SymfonyCartography\Service\Categorizer\ClassCategory::controller
        color: '#4091c9'
      - class: !php/enum Micoli\SymfonyCartography\Service\Categorizer\ClassCategory::messengerCommandHandler
        color: '#9dcee2'
      - class: !php/enum Micoli\SymfonyCartography\Service\Categorizer\ClassCategory::messengerEventListener
        color: '#fedfd4'
      - class: !php/enum Micoli\SymfonyCartography\Service\Categorizer\ClassCategory::messengerEvent
        color: '#f29479'
      - class: !php/enum Micoli\SymfonyCartography\Service\Categorizer\ClassCategory::messengerCommand
        color: '#f26a4f'
      - class: !php/enum Micoli\SymfonyCartography\Service\Categorizer\ClassCategory::symfonyConsoleCommand
        color: '#ef3c2d'
      - class: !php/enum Micoli\SymfonyCartography\Service\Categorizer\ClassCategory::symfonyEventListener
        color: '#cb1b16'
      - class: !php/enum Micoli\SymfonyCartography\Service\Categorizer\ClassCategory::doctrineRepository
        color: '#65010c'
      - class: !php/enum Micoli\SymfonyCartography\Service\Categorizer\ClassCategory::symfonyEvent
        color: '#f29479'
```

Usage
-----
- in `webProfiler` toolbar you can see a preview of the call graph from the current controller
![toolbar](docs/toolbar.png)
- in `webProfiler` page a new section called `cartography` is present
![toolbar](docs/profiler.png)

Tests
-----

Execute this command to run tests:

```bash
$ make tests-all
```

# Todo
-----

- manage symfony events and handler
- manage symfony services
- indirect from interfaces or use service definitions
- get called tree and calling tree

