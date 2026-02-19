<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DishRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DishRepository::class)]
class Dish
{
    public const TYPE_ENTREE  = 'entree';
    public const TYPE_PLAT    = 'plat';
    public const TYPE_DESSERT = 'dessert';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    /**
     * Valeurs attendues: entree | plat | dessert
     */
    #[ORM\Column(length: 20)]
    private ?string $type = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->isActive  = true;
    }

    public static function allowedTypes(): array
    {
        return [self::TYPE_ENTREE, self::TYPE_PLAT, self::TYPE_DESSERT];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $name = trim($name);
        if ($name === '') {
            throw new \InvalidArgumentException('Dish name cannot be empty.');
        }

        $this->name = $name;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $type = strtolower(trim($type));
        if (!in_array($type, self::allowedTypes(), true)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid Dish type "%s". Allowed: %s',
                $type,
                implode(', ', self::allowedTypes())
            ));
        }

        $this->type = $type;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description ? trim($description) : null;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt ?? new \DateTimeImmutable();
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
