<?php

namespace App\Entity;

use App\Repository\AttributeValueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AttributeValueRepository::class)]
class AttributeValue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $value = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $unit = null;

    /**
     * @var Collection<int, AttributeName>
     */
    #[ORM\ManyToMany(targetEntity: AttributeName::class, mappedBy: 'value')]
    private Collection $attributeNames;

    /**
     * @var Collection<int, ProductVariant>
     */
    #[ORM\ManyToMany(targetEntity: ProductVariant::class, mappedBy: 'attributes')]
    private Collection $productVariants;

    public function __construct()
    {
        $this->attributeNames = new ArrayCollection();
        $this->productVariants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(?string $unit): static
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * @return Collection<int, AttributeName>
     */
    public function getAttributeNames(): Collection
    {
        return $this->attributeNames;
    }

    public function addAttributeName(AttributeName $attributeName): static
    {
        if (!$this->attributeNames->contains($attributeName)) {
            $this->attributeNames->add($attributeName);
            $attributeName->addValue($this);
        }

        return $this;
    }

    public function removeAttributeName(AttributeName $attributeName): static
    {
        if ($this->attributeNames->removeElement($attributeName)) {
            $attributeName->removeValue($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, ProductVariant>
     */
    public function getProductVariants(): Collection
    {
        return $this->productVariants;
    }

    public function addProductVariant(ProductVariant $productVariant): static
    {
        if (!$this->productVariants->contains($productVariant)) {
            $this->productVariants->add($productVariant);
            $productVariant->addAttribute($this);
        }

        return $this;
    }

    public function removeProductVariant(ProductVariant $productVariant): static
    {
        if ($this->productVariants->removeElement($productVariant)) {
            $productVariant->removeAttribute($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getAttributeNames()->first() . ' ' . $this->getValue(). ' ' . $this->getUnit() ;
    }


}
