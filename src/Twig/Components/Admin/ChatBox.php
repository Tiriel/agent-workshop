<?php

namespace App\Twig\Components\Admin;

use App\Form\MessageType;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerHelper;
use Symfony\Component\DependencyInjection\Attribute\AutowireMethodOf;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsLiveComponent]
class ChatBox
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp]
    public string $title = '';

    #[LiveProp]
    public bool $isOpen = false;

    public function __construct(
        #[AutowireMethodOf(ControllerHelper::class)]
        private readonly \Closure $createForm,
    ) {}

    #[LiveAction]
    public function toggle(): void
    {
        $this->isOpen = !$this->isOpen;
    }

    protected function instantiateForm(): FormInterface
    {
        return ($this->createForm)(MessageType::class);
    }

    #[ExposeInTemplate]
    public function getMessages(): array
    {
        return [];
    }
}
