<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use App\Entity\MenuImage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MenuRepository::class)]
class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $conditions = null;

    #[ORM\Column(length: 80, nullable: true)]
    private ?string $themeLabel = null;

    #[ORM\Column]
    private ?int $minPeople = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0)]
    private ?string $minPrice = null;

    #[ORM\Column(nullable: true)]
    private ?int $stock = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isActive = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;
    #[ORM\OneToMany(mappedBy: 'menu', targetEntity: MenuImage::class, orphanRemoval: true, cascade: ['persist'])]
private Collection $images;


/** @return Collection<int, MenuImage> */
public function getImages(): Collection
{
    return $this->images;
}

public function addImage(MenuImage $image): static
{
    if (!$this->images->contains($image)) {
        $this->images->add($image);
        $image->setMenu($this);
    }
    return $this;
}

public function removeImage(MenuImage $image): static
{
    if ($this->images->removeElement($image)) {
        if ($image->getMenu() === $this) {
            $image->setMenu(null);
        }
    }
    return $this;
}

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'menu')]
    private Collection $orders;

    public function __construct()
    {
      $this->orders = new ArrayCollection();
    $this->images = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getConditions(): ?string
    {
        return $this->conditions;
    }

    public function setConditions(?string $conditions): static
    {
        $this->conditions = $conditions;

        return $this;
    }

    public function getThemeLabel(): ?string
    {
        return $this->themeLabel;
    }

    public function setThemeLabel(?string $themeLabel): static
    {
        $this->themeLabel = $themeLabel;

        return $this;
    }

    public function getMinPeople(): ?int
    {
        return $this->minPeople;
    }

    public function setMinPeople(int $minPeople): static
    {
        $this->minPeople = $minPeople;

        return $this;
    }

    public function getMinPrice(): ?string
    {
        return $this->minPrice;
    }

    public function setMinPrice(string $minPrice): static
    {
        $this->minPrice = $minPrice;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): static
    {
        $this->isActive = $isActive;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setMenu($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getMenu() === $this) {
                $order->setMenu(null);
            }
        }

        return $this;
    }
}
