<?php

namespace App\Ai\Tool;

use App\Enum\PostStatus;
use App\Repository\PostRepository;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(name: 'status_search', description: 'Counts posts in database based on a spefici status')]
class StatusSearch
{
    public function __construct(
        private readonly PostRepository $repository,
    ) {}

    public function __invoke(string $status): string
    {
        try {
            $count = $this->repository->count(['status' => PostStatus::from($status)]);
        } catch (\ValueError $error) {
            return "Invalid status. Allowed: draft, published, archived.";
        }

        return sprintf('Found %d posts with status %s', $count, $status);
    }
}
