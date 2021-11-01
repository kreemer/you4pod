<?php

namespace App\Entity;

use App\Repository\AbonnementRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AbonnementRepository::class)
 */
class Abonnement
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $name = null;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $url = null;

    /**
     * @ORM\Column(type="string")
     */
    private string $quality = 'high';
    /**
     * @ORM\Column(type="string")
     */
    private string $format = 'video';

    /**
     * @ORM\OneToOne(targetEntity=Channel::class, cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $channel;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return Abonnement
     */
    public function setName(?string $name): Abonnement
    {
        $this->name = $name;
        return $this;
    }


    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     * @return Abonnement
     */
    public function setUrl(?string $url): Abonnement
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getQuality(): string
    {
        return $this->quality;
    }

    /**
     * @param string $quality
     * @return Abonnement
     */
    public function setQuality(string $quality): Abonnement
    {
        $this->quality = $quality;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @param string $format
     * @return Abonnement
     */
    public function setFormat(string $format): Abonnement
    {
        $this->format = $format;
        return $this;
    }

    public function getChannel(): ?Channel
    {
        return $this->channel;
    }

    public function setChannel(Channel $channel): self
    {
        $this->channel = $channel;

        return $this;
    }

}
