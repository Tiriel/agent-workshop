<?php

namespace App\Command;

use App\Repository\PostRepository;
use Symfony\AI\Store\Document\Metadata;
use Symfony\AI\Store\Document\TextDocument;
use Symfony\AI\Store\IndexerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:index:posts',
    description: 'Index all posts.',
)]
class IndexPostsCommand extends Command
{
    public function __construct(
        private readonly IndexerInterface $indexer,
        private readonly PostRepository $repository,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $count = $this->repository->count();
        
        for ($i = 0; $i < $count; $i += 10) {
            $documents = [];
            $posts = $this->repository->findBy([], [], 10, $i);
            foreach ($posts as $post) {
                $documents[] = new TextDocument(
                    id: $post->getId(),
                    content: sprintf("Title: %s\nAuthor: %s %s\nContent:\n%s",
                        $post->getTitle(),
                        $post->getAuthor()->getFirstname(),
                        $post->getAuthor()->getLastname(),
                        $post->getContent()
                    ),
                    metadata: new Metadata($post)
                );
            }

            $this->indexer->index($documents);
        }

        $io->success('Finished indexing posts.');

        return Command::SUCCESS;
    }
}
