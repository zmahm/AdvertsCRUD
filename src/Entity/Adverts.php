<?php

namespace App\Entity;

use App\Repository\AdvertRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AdvertRepository::class)]
class Adverts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Title is required.", groups: ['Default', 'creation', 'edit'])]
    #[Assert\Length(
        max: 255,
        maxMessage: "Title cannot be longer than {{ limit }} characters.",
        groups: ['Default', 'creation', 'edit']
    )]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Description is required.", groups: ['Default', 'creation', 'edit'])]
    #[Assert\Length(
        max: 255,
        maxMessage: "Description cannot be longer than {{ limit }} characters.",
        groups: ['Default', 'creation', 'edit']
    )]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: "Price is required.", groups: ['creation', 'edit'])]
    #[Assert\Regex(
        pattern: '/^\d+(\.\d{1,2})?$/',
        message: "The price must be a valid number with up to two decimal places.",
        groups: ['creation', 'edit']
    )]
    private ?string $price = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Location is required.", groups: ['creation', 'edit'])]
    #[Assert\Length(
        max: 255,
        maxMessage: "Location cannot be longer than {{ limit }} characters.",
        groups: ['creation', 'edit']
    )]
    private ?string $location = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\OneToMany(targetEntity: AdvertImage::class, mappedBy: 'advert', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Assert\Count(
        max: 4,
        maxMessage: "You can only upload up to {{ limit }} images.",
        groups: ['creation', 'edit']
    )]
    private Collection $advertImages;

    public function __construct()
    {
        $this->advertImages = new ArrayCollection();
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

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, AdvertImage>
     */
    public function getAdvertImages(): Collection
    {
        return $this->advertImages;
    }

    public function addAdvertImage(AdvertImage $advertImage): static
    {
        if (!$this->advertImages->contains($advertImage)) {
            $this->advertImages->add($advertImage);
            $advertImage->setAdvert($this);
        }

        return $this;
    }

    public function removeAdvertImage(AdvertImage $advertImage): static
    {
        if ($this->advertImages->removeElement($advertImage)) {
            // Remove the associated file if the image is no longer used
            if ($advertImage->getAdvert() === $this) {
                $advertImage->setAdvert(null);
            }
        }

        return $this;
    }

}
