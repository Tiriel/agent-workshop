<?php

namespace App\Twig\Components\Admin;

use App\Form\MessageType;
use Symfony\AI\Chat\ChatInterface;
use Symfony\AI\Chat\MessageStoreInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
class ChatBox extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp]
    public string $title = '';

    #[LiveProp]
    public bool $isOpen;

    public function __construct(
        private readonly ChatInterface $chat,
        private readonly MessageStoreInterface $store,
        private readonly HubInterface $hub,
    ) {
    }

    public function mount(): void
    {
        $this->isOpen ??= false;
    }

    #[LiveAction]
    public function save(): void
    {
        $this->submitForm();
        $data = $this->getForm()->getData();
        $message = Message::ofUser($data['content']);

        $this->hub->publish(new Update(
            'chat_messages',
            $this->renderBlock('broadcast/Message.stream.html.twig', 'create', ['entity' => $message]),
        ));
        $this->chat->submit($message);

        $this->resetForm();
    }

    #[LiveAction]
    public function toggle(): void
    {
        $this->isOpen = !$this->isOpen;
    }

    #[ExposeInTemplate]
    public function getMessages(): array
    {
        return $this->store->load()->getMessages();
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(MessageType::class);
    }
}
