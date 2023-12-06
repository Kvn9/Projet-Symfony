<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?float $prize = null;

    #[ORM\Column]
    private ?int $stock = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picture = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: ContentCart::class, orphanRemoval: true)]
    private Collection $contentCarts;

    public function __construct()
    {
        $this->contentCarts = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrize(): ?float
    {
        return $this->prize;
    }

    public function setPrize(float $prize): static
    {
        $this->prize = $prize;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): static
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * @return Collection<int, ContentCart>
     */
    public function getContentCarts(): Collection
    {
        return $this->contentCarts;
    }

    public function addContentCart(ContentCart $contentCart): static
    {
        if (!$this->contentCarts->contains($contentCart)) {
            $this->contentCarts->add($contentCart);
            $contentCart->setProduct($this);
        }

        return $this;
    }

    public function removeContentCart(ContentCart $contentCart): static
    {
        if ($this->contentCarts->removeElement($contentCart)) {
            // set the owning side to null (unless already changed)
            if ($contentCart->getProduct() === $this) {
                $contentCart->setProduct(null);
            }
        }

        return $this;
    }
}
