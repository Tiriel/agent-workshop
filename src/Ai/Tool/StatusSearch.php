<?php

namespace App\Ai\Tool;

use App\Enum\PostStatus;
use App\Repository\PostRepository;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(
    name: 'status_search',
    description: <<<EOD
Performs a search to count posts having a specific status.
Available statuses:
* published
* draft
* archived
EOD

)]
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
            return sprintf("Status %s is not a valid status. Available statuses: draft, published, archived.", $status);
        }

        return sprintf("Found %d posts with status %s", $count, $status);
    }
}
