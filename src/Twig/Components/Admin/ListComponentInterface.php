<?php

namespace App\Twig\Components\Admin;

interface ListComponentInterface
{
    public bool $paginated {
        get;
    }

    public string $entityName {
        get;
    }

    public function getEntities(): iterable;

    public function getColumns(): array;
}
