<?php

namespace App\Twig\Components\Admin;

use App\Form\MessageType;
use Symfony\AI\Chat\ChatInterface;
use Symfony\AI\Chat\MessageStoreInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerHelper;
use Symfony\Component\DependencyInjection\Attribute\AutowireMethodOf;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
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
        #[AutowireMethodOf(ControllerHelper::class)]
        private readonly \Closure $renderBlock,
        private readonly HubInterface $hub,
        private readonly ChatInterface $chat,
        private readonly MessageStoreInterface $store,
    ) {}

    protected function instantiateForm(): FormInterface
    {
        return ($this->createForm)(MessageType::class);
    }

    #[LiveAction]
    public function toggle(): void
    {
        $this->isOpen = !$this->isOpen;
    }

    #[LiveAction]
    public function save(): void
    {
        $this->submitForm();
        $message = Message::ofUser($this->getForm()->getData()['content']);
        $this->hub->publish(new Update(
            'chat_messages',
            ($this->renderBlock)(
                'broadcast/Message.stream.html.twig',
                'create',
                ['entity' => $message],
            )
        ));
        $this->chat->submit($message);
        $this->resetForm();
    }

    #[ExposeInTemplate]
    public function getMessages(): array
    {
        return $this->store->load()->getMessages();
    }
}
