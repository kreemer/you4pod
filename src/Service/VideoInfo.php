<?php

namespace App\Service;

class VideoInfo
{

    public function __construct(
        public string $title,
        public string $author,
        public string $thumbnail,
        public int $duration,
        public string $description,
        public \DateTime $date,
        public string $ext
    )
    {
    }
}