<?php

namespace App\Service;

use App\Entity\Abonnement;
use Cocur\Slugify\SlugifyInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\RouterInterface;

class YoutubeDownloaderService
{

    private string $downloadDir;
    private SlugifyInterface $slugify;

    private string $youtubeDlPath;
    private string $host;

    public function __construct(string $downloadDir, string $host, SlugifyInterface $slugify)
    {
        $this->downloadDir = $downloadDir;
        $this->slugify = $slugify;
        $this->host = $host;

        $executableFinder = new ExecutableFinder();
        $this->youtubeDlPath = $executableFinder->find('yt-dlp');

        if ($this->youtubeDlPath === null) {
            throw new \RuntimeException('No yt-dlp found in path');
        }

    }

    public function download(Abonnement $abonnement, $chunkSize = 1): void
    {
        $basePath = $this->getBasePath($abonnement);

        $filesystem = new Filesystem();
        $filesystem->mkdir($basePath);

        $process = new Process([
            $this->youtubeDlPath,
            '--download-archive',
            $basePath . DIRECTORY_SEPARATOR . '.download-archive.txt',
            '-o',
            $basePath . DIRECTORY_SEPARATOR . '%(id)s.%(ext)s',
            '--max-download',
            $chunkSize,
            '--playlist-reverse',
            '--write-info-json',
            $abonnement->getUrl()
        ]);
        $process->setTimeout(3600);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful() && $process->getExitCode() !== 101) {
            throw new ProcessFailedException($process);
        }
    }

    public function getBaseUrl(Abonnement $abonnement): string
    {
        return $this->host . $this->slugify->slugify($abonnement->getId() . '-' . $abonnement->getName());
    }

    public function getBasePath(Abonnement $abonnement): string
    {
        return $this->downloadDir . DIRECTORY_SEPARATOR . $this->slugify->slugify($abonnement->getId() . '-' . $abonnement->getName());
    }
}