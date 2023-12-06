<?php

namespace App\Entity;

use App\Repository\CartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CartRepository::class)]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(
        min: 1,
        max: 255,
        minMessage: "Le nom doit contenir 1 caractère au minimun",
        maxMessage: "Le nom ne doit pas contenir plus que 255 caractères"
    )]

    #[ORM\ManyToOne(inversedBy: 'carts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $buyAt = null;

    #[ORM\Column(nullable: true)]
    private ?bool $state = null;

    #[ORM\OneToMany(mappedBy: 'cart', targetEntity: ContentCart::class)]
    private Collection $contentCarts;

    public function __construct()
    {
        $this->contentCarts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getBuyAt(): ?\DateTimeInterface
    {
        return $this->buyAt;
    }

    public function setBuyAt(?\DateTimeInterface $buyAt): static
    {
        $this->buyAt = $buyAt;

        return $this;
    }

    public function isState(): ?bool
    {
        return $this->state;
    }

    public function setState(?bool $state): static
    {
        $this->state = $state;

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
            $contentCart->setCart($this);
        }

        return $this;
    }

    public function removeContentCart(ContentCart $contentCart): static
    {
        if ($this->contentCarts->removeElement($contentCart)) {
            // set the owning side to null (unless already changed)
            if ($contentCart->getCart() === $this) {
                $contentCart->setCart(null);
            }
        }

        return $this;
    }
}
