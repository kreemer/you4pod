<?php

namespace App\Service;

class AbonnementInfo
{
    public ?string $description = null;

    public function __construct(
        public string $type,
        public string $id,
        public string $title,
        public string $url
    )
    { }
}