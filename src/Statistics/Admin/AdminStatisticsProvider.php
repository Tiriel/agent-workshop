<?php

namespace App\Statistics\Admin;

use App\Enum\PostStatus;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;

final class AdminStatisticsProvider
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PostRepository $postRepository,
        private readonly TagRepository $tagRepository,
    ) {
    }

    public function getUserStatistics(): array
    {
        $totalUsers = $this->userRepository->count([]);
        $totalPosts = $this->postRepository->count([]);
        $averagePostsPerUser = $totalUsers > 0 ? round($totalPosts / $totalUsers, 2) : 0;

        return [
            'total_count' => $totalUsers,
            'average_posts_per_user' => $averagePostsPerUser,
        ];
    }

    public function getPostStatistics(): array
    {
        $totalPosts = $this->postRepository->count([]);

        $countByStatus = [];
        foreach (PostStatus::cases() as $status) {
            $countByStatus[$status->value] = $this->postRepository->count(['status' => $status]);
        }

        $countWithoutTags = $this->postRepository->countWithoutTags();
        $oldestPost = $this->postRepository->findOneBy([], ['createdAt' => 'ASC']);
        $latestCreated = $this->postRepository->findOneBy([], ['createdAt' => 'DESC']);
        $latestPublished = $this->postRepository->findOneBy([], ['publishedAt' => 'DESC']);
        $averageTagsPerPost = $this->postRepository->getAverageTagsPerPost();

        return [
            'total_count' => $totalPosts,
            'count_by_status' => $countByStatus,
            'count_without_tags' => $countWithoutTags,
            'oldest_date' => $oldestPost?->getCreatedAt(),
            'latest_created_date' => $latestCreated?->getCreatedAt(),
            'latest_published_date' => $latestPublished?->getPublishedAt(),
            'average_tags_per_post' => $averageTagsPerPost,
        ];
    }

    public function getTagStatistics(): array
    {
        $totalTags = $this->tagRepository->count([]);
        $unusedTagsCount = $this->tagRepository->countUnused();

        return [
            'total_count' => $totalTags,
            'unused_count' => $unusedTagsCount,
        ];
    }
}

