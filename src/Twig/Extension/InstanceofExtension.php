<?php

namespace App\Twig\Extension;

use Twig\Attribute\AsTwigTest;
use Twig\TwigFilter;
use Twig\TwigFunction;

class InstanceofExtension
{
    #[AsTwigTest('instanceof')]
    public function instanceof(mixed $value, string $class): bool
    {
        return $value instanceof $class;
    }
}
