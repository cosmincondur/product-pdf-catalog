<?php

namespace App\Entity;

use App\Repository\AttributeNameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AttributeNameRepository::class)]
class AttributeName
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, AttributeValue>
     */
    #[ORM\ManyToMany(targetEntity: AttributeValue::class, inversedBy: 'attributeNames')]
    private Collection $value;

    public function __construct()
    {
        $this->value = new ArrayCollection();
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

    /**
     * @return Collection<int, AttributeValue>
     */
    public function getValue(): Collection
    {
        return $this->value;
    }

    public function addValue(AttributeValue $value): static
    {
        if (!$this->value->contains($value)) {
            $this->value->add($value);
        }

        return $this;
    }

    public function removeValue(AttributeValue $value): static
    {
        $this->value->removeElement($value);

        return $this;
    }

    public function __toString(): string
    {
        return $this->getName();
    }


}
