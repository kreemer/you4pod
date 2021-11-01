<?php

namespace App\Command;

use App\Entity\Abonnement;
use App\Entity\Channel;
use App\Repository\AbonnementRepository;
use App\Service\YoutubeDownloaderService;
use App\Service\YoutubeService;
use Doctrine\ORM\EntityManagerInterface;
use Lukaswhite\FeedWriter\Itunes;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\RouterInterface;

class SyncCommand extends Command
{
    protected static $defaultName = 'app:sync';
    protected static $defaultDescription = 'Add a short description for your command';

    private YoutubeService $youtubeService;
    private YoutubeDownloaderService $youtubeDownloaderService;
    private AbonnementRepository $abonnementRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(YoutubeService $youtubeService, YoutubeDownloaderService $youtubeDownloaderService, AbonnementRepository $abonnementRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->youtubeService = $youtubeService;
        $this->youtubeDownloaderService = $youtubeDownloaderService;
        $this->abonnementRepository = $abonnementRepository;
        $this->entityManager = $entityManager;
    }


    protected function configure(): void
    {
        $this->addOption(
            'refreshChannel',
            'r',
            InputOption::VALUE_NONE,
            'refresh channel data?'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->text('Start syncing');

        $abonnements = $this->abonnementRepository->findAll();
        foreach ($abonnements as $abonnement) {
            $io->text('Processing ' . $abonnement->getName());

            if ($abonnement->getChannel() === null || $input->getOption('refreshChannel')) {
                $type = $this->youtubeService->enumerateType($abonnement->getUrl());
                if ($type === null) {
                    $io->error('Could not fetch metadata for channel, continue with next abonnement');
                    continue;
                }
                switch($type['type']) {
                    case 'playlist':
                        $playlistInfo = $this->youtubeService->getPlaylistById($type['id']);

                        $item = current($playlistInfo['items']);


                        $image = $item['snippet']['thumbnails']['maxres']['url'] ??
                            $item['snippet']['thumbnails']['standard']['url'] ??
                            $item['snippet']['thumbnails']['high']['url'] ??
                            $item['snippet']['thumbnails']['medium']['url'] ??
                            $item['snippet']['thumbnails']['default']['url'];

                        $channel = $abonnement->getChannel() ?? new Channel();
                        $channel->setTitle($item['snippet']['title'])
                            ->setDescription($item['snippet']['description'])
                            ->setSummary($item['snippet']['description'])
                            ->setLink('https://youtube.com/playlist?list=' . $item['id'])
                            ->setTtl(3600)
                            ->setImage($image)
                            ->setLastBuildDate(new \DateTime());

                        $abonnement->setChannel($channel);

                        $this->entityManager->persist($channel);
                        $this->entityManager->flush();
                        break;
                    default:
                        $io->error('Type for abonnement is not processable: ' . $type['type']);
                }
            }

            $this->youtubeDownloaderService->download($abonnement);
        }

        return Command::SUCCESS;
    }

    /**
     * $io->section('Getting metaInfo for abonnement `' . $abonnement->getName() . '`');

    $info = $this->youtubeDownloaderService->info($abonnement);
    continue;
    $io->horizontalTable(['type', 'id', 'title', 'description', 'url'], [
    [
    $info->type,
    $info->id,
    $info->title,
    $info->description,
    $info->url
    ]
    ]);

    $progressBar = new ProgressBar($output, $abonnement->getPageSize());

    $progressBar->start();
    for ($i = 0; $i < $abonnement->getPageSize(); $i++) {
    $this->youtubeDownloaderService->download($abonnement);


    $progressBar->advance();
    }
    $progressBar->finish();

    $io->newLine();


    $idList = $this->youtubeDownloaderService->readArchiveContent($abonnement);
    $feed = new Itunes();
    $channel = $feed->addChannel();

    $channel->title($info->title)
    ->description($info->description ?? $info->title)
    ->summary($info->description ?? $info->title)
    ->link($info->url)
    ->ttl(60)
    ->lastBuildDate(new \DateTime());

    foreach ($idList as $id) {
    $videoInfo = $this->youtubeDownloaderService->readVideoInfo($abonnement, $id);
    if ($videoInfo === null) {
    $io->error('Failed to read video info for id: ' . $id);
    continue;
    }
    $channel->addItem()
    ->title($videoInfo->title)
    ->author($videoInfo->author)
    ->duration($videoInfo->duration)
    ->summary($videoInfo->description)
    ->pubDate($videoInfo->date)
    ->guid($this->youtubeDownloaderService->getBaseUrl($abonnement) . DIRECTORY_SEPARATOR . $id. '.' . $videoInfo->ext)
    ->addEnclosure()
    ->url($this->youtubeDownloaderService->getBaseUrl($abonnement) . DIRECTORY_SEPARATOR . $id. '.' . $videoInfo->ext)
    ->length(filesize($this->youtubeDownloaderService->getBasePath($abonnement) . DIRECTORY_SEPARATOR . $id . '.' . $videoInfo->ext))
    ->type('mp4');
    }

    file_put_contents($this->youtubeDownloaderService->getBasePath($abonnement) . 'podcast.rss', $feed->toString());

    $io->text('Finished');
     */
}
