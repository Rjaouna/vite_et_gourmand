<?php

namespace App\Entity;

use App\Entity\Menu;
use App\Repository\MenuImageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuImageRepository::class)]
class MenuImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imagePath = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $altText = null;

    #[ORM\Column(nullable: true)]
    private ?int $position = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isCover = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;
    #[ORM\ManyToOne(inversedBy: 'images')]
#[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
private ?Menu $menu = null;

public function getMenu(): ?Menu
{
    return $this->menu;
}

public function setMenu(?Menu $menu): static
{
    $this->menu = $menu;
    return $this;
}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): static
    {
        $this->imagePath = $imagePath;

        return $this;
    }

    public function getAltText(): ?string
    {
        return $this->altText;
    }

    public function setAltText(?string $altText): static
    {
        $this->altText = $altText;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function isCover(): ?bool
    {
        return $this->isCover;
    }

    public function setIsCover(?bool $isCover): static
    {
        $this->isCover = $isCover;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
