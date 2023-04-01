<?php

declare(strict_types=1);

namespace Micoli\SymfonyCartography\Service\Categorizer;

enum ClassCategory implements ClassCategoryInterface
{
    case undefined;
    case doctrineEntity;
    case controller;
    case messengerCommandHandler;
    case messengerEventListener;

    case messengerEvent;

    case messengerCommand;

    case symfonyConsoleCommand;

    case symfonyEventListener;

    case doctrineRepository;

    case symfonyEvent;

    public function asText(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->name;
    }
}
