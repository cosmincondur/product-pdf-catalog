<?php

namespace App\Entity;

use App\Repository\ProductVariantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ProductVariantRepository::class)]
#[UniqueEntity(
    fields: ['sku'],
    message: 'This SKU is already in use.'
)]
class ProductVariant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $sku = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $price = null;

    #[ORM\ManyToOne(inversedBy: 'variants')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Product $parent = null;

    /**
     * @var Collection<int, AttributeValue>
     */
    #[ORM\ManyToMany(targetEntity: AttributeValue::class, inversedBy: 'productVariants')]
    private Collection $attributes;

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
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
        $this->name = $name;

        return $this;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(string $sku): static
    {
        $this->sku = $sku;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price !== null ? (float)$this->price : null;
    }

    public function setPrice(?float $price): static
    {
        $this->price = $price !== null ? (string)$price : null;

        return $this;
    }

    public function getParent(): ?Product
    {
        return $this->parent;
    }

    public function setParent(?Product $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, AttributeValue>
     */
    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    public function addAttribute(AttributeValue $attribute): static
    {
        if (!$this->attributes->contains($attribute)) {
            $this->attributes->add($attribute);
        }

        return $this;
    }

    public function removeAttribute(AttributeValue $attribute): static
    {
        $this->attributes->removeElement($attribute);

        return $this;
    }

    public function __toString(): string
    {
         return $this->getName();
    }


}
