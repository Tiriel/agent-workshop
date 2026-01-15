<?php

namespace App\Ai\Tool;

use App\Enum\PostStatus;
use App\Repository\PostRepository;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(name: 'status_search', description: 'Counts posts with a specific status')]
final class StatusSearch
{
    private array $usedDocuments;

    public function __construct(
        private readonly PostRepository $postRepository,
    ) {
    }

    public function __invoke(string $status): string
    {
        try {
            $count = $this->postRepository->count(['status' => PostStatus::from($status)]);
        } catch (\ValueError $error) {
            return \sprintf(
                'The status %s does not exist. (Allowed statuses : [%s])',
                $status,
                \implode(', ', \array_map(fn(PostStatus $s) => $s->getLabel(), PostStatus::cases()))
            );
        }

        return \sprintf('Found %d posts with status "%s"', $count, $status);
    }
}
