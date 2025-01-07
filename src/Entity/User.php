<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\Table(name: 'user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Email is required.', groups: ['Default', 'registration', 'login'])]
    #[Assert\Email(message: 'Please enter a valid email address.', groups: ['Default', 'registration', 'login'])]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = []; // The user roles

    #[ORM\Column]
    private ?string $password = null; // The hashed password

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Name is required.', groups: ['Default', 'registration'])]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Name must be at least {{ limit }} characters.',
        maxMessage: 'Name cannot be longer than {{ limit }} characters.',
        groups: ['Default', 'registration']
    )]
    private ?string $name = null;

    // Non-persistent property for plain password input. Used up until hashed at registration and then cleared with eraseCredentials()
    #[Assert\NotBlank(message: 'Password is required.', groups: ['registration','login'])]
    #[Assert\Length(
        min: 6,
        max: 50,
        minMessage: 'Password must be at least {{ limit }} characters.',
        maxMessage: 'Password cannot be longer than {{ limit }} characters.',
        groups: ['registration']
    )]
    private ?string $plainPassword = null;

    // Getters and setters for id, email, roles, password, name, and plainPassword.

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // Clear plainPassword after processing
        $this->plainPassword = null;
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

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }
}
