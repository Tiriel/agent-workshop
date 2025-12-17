<?php

namespace App\Controller\Admin;

use App\Repository\PostRepository;
use App\Statistics\Admin\AdminStatisticsProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/admin', name: 'app_admin_dashboard')]
    public function index(
        PostRepository $postRepository,
        AdminStatisticsProvider $statisticsProvider
    ): Response {
        // Latest post
        $latestPost = $postRepository->findOneBy([], ['createdAt' => 'DESC']);

        // Statistics
        $userStats = $statisticsProvider->getUserStatistics();
        $postStats = $statisticsProvider->getPostStatistics();
        $tagStats = $statisticsProvider->getTagStatistics();

        return $this->render('admin/dashboard/index.html.twig', [
            'latest_post' => $latestPost,
            'user_stats' => $userStats,
            'post_stats' => $postStats,
            'tag_stats' => $tagStats,
        ]);
    }
}
