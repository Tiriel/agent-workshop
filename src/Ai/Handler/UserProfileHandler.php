<?php

namespace App\Ai\Handler;

use App\Entity\User;
use Symfony\AI\Platform\Bridge\Anthropic\Claude;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UserProfileHandler
{
    public function __construct(
        #[CurrentUser]
        private readonly User $user,
    ) {}

    public function getPreferredModel(): string
    {
        $model = $this->user->getProfile()->getModel();

        return match ($model) {
            'claude' => Claude::HAIKU_35,
        };
    }
}
