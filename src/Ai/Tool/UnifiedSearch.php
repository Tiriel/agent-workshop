<?php

namespace App\Ai\Tool;

use App\Entity\Post;
use App\Enum\PostStatus;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(
    name: 'unified_search',
    description: <<<EOD
A tool to perform a search on posts based on multiple criteria.
Available criteria:
* status (draft, published, or archived)
* creation date (called 'createdAt', a date string)
* author name
Criteria representation: A string representing an array of criteria. All keys can be omitted, at least one key must be passed.
Representation example:
{
    'status': 'draft',
    'createdAt': '2026-03-26',
    'author': 'Some name'
}
EOD

)]
class UnifiedSearch
{
    public function __construct(
        private readonly PostRepository $postRepository,
        private readonly UserRepository $userRepository,
    ) {}

    public function __invoke(string $criteria): string
    {
        $criteria = \json_decode($criteria, true);
        if (\array_key_exists('status', $criteria)) {
            try {
                $criteria['status'] = PostStatus::from($criteria['status']);
            } catch (\ValueError $e) {
                return sprintf("Status %s is not a valid status. Available statuses: draft, published, archived.", $status);
            }
        }

        if (\array_key_exists('createdAt', $criteria)) {
            $criteria['createdAt'] = new \DateTimeImmutable($criteria['createdAt']);
        }

        if (\array_key_exists('author', $criteria)) {
            [$firstname, $lastname] = explode(' ', $criteria['author']);
            $criteria['author'] = $this->userRepository->findOneBy(['firstname' => $firstname, 'lastname' => $lastname]);
        }

        $result = $this->postRepository->findBy($criteria);
        \array_map(function (Post $post) {
            return sprintf("* id: %s - Title: %s", $post->getId(), $post->getTitle());
        }, $result);

        return implode("\n", $result);
    }
}
