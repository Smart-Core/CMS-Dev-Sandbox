<?php

declare(strict_types=1);

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;
use SmartCore\RadBundle\Doctrine\ColumnTrait;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

abstract class UserModel implements UserInterface, PasswordAuthenticatedUserInterface
{
    use ColumnTrait\CreatedAt;
    use ColumnTrait\IsEnabled;

    #[ORM\Column(type: 'string', length: 40)]
    #[Assert\NotNull(message: 'This value is not valid.')]
    #[Assert\Length(min: 3, minMessage: 'Username length must be at least {{ limit }} characters long')]
    protected string $username;

    #[ORM\Column(type: 'string', unique: true, length: 40)]
    protected string $username_canonical;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank()]
    #[Assert\Email(mode: 'strict')]
    protected string $email;

    #[ORM\Column(type: 'string', unique: true, length: 100)]
    protected string $email_canonical;

    #[ORM\Column(type: 'string', length: 190)]
    protected string $password;

    // Plain password. Used for model validation. Must not be persisted.
    #[Assert\Length(min: 6, minMessage: 'Password length must be at least {{ limit }} characters long')]
    protected ?string $plain_password = null;

    #[ORM\Column(type: 'array')]
    protected array $roles;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?\DateTime $last_login;

    public function __construct()
    {
        $this->created_at   = new \DateTimeImmutable();
        $this->is_enabled   = false;
        $this->password     = '';
        $this->roles        = [];
        $this->username     = '';
    }

    public function serialize(): string
    {
        return serialize([
            $this->id,
            $this->is_enabled,
            $this->email,
            $this->email_canonical,
            $this->username,
            $this->username_canonical,
            $this->password
        ]);
    }

    public function unserialize(string $serialized): void
    {
        [
            $this->id,
            $this->is_enabled,
            $this->email,
            $this->email_canonical,
            $this->username,
            $this->username_canonical,
            $this->password
        ] = unserialize($serialized, ['allowed_classes' => false]);
    }

    static public function canonicalize(string $string): ?string
    {
        if (null === $string) {
            return null;
        }

        $encoding = mb_detect_encoding($string);
        $result = $encoding
            ? mb_convert_case($string, MB_CASE_LOWER, $encoding)
            : mb_convert_case($string, MB_CASE_LOWER);

        return $result;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * {@inheritdoc}
     */
    public function getSalt(): ?string
    {
        // See "Do you need to use a Salt?" at https://symfony.com/doc/current/cookbook/security/entity_provider.html
        // we're using bcrypt in security.yml to encode the password, so
        // the salt value is built-in and you don't have to generate one

        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
        // if you had a plainPassword property, you'd nullify it here
        $this->plain_password = null;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('ROLE_SUPER_ADMIN');
    }

    public function setSuperAdmin(bool $boolean): self
    {
        if (true === $boolean) {
            $this->addRole('ROLE_SUPER_ADMIN');
        } else {
            $this->removeRole('ROLE_SUPER_ADMIN');
        }

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = trim($email);
        $this->email_canonical = self::canonicalize(trim($email));

        return $this;
    }

    public function getEmailCanonical(): string
    {
        return $this->email_canonical;
    }

    public function setEmailCanonical(string $email_canonical): self
    {
        $this->email_canonical = $email_canonical;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = trim($username);
        $this->username_canonical = self::canonicalize($this->username);

        return $this;
    }

    public function getUsernameCanonical(): string
    {
        return $this->username_canonical;
    }

    public function setUsernameCanonical(string $username_canonical): self
    {
        $this->username_canonical = $username_canonical;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plain_password;
    }

    public function setPlainPassword(?string $plain_password): self
    {
        $this->plain_password = $plain_password;

        return $this;
    }

    public function addRole(string $role): self
    {
        $role = strtoupper($role);
        if ($role === 'ROLE_USER') {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(string $role): self
    {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    public function hasRole(string $role): bool
    {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    /**
     * Returns the roles or permissions granted to the user for security.
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        // guarantees that a user always has at least one role for security
        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getLastLogin(): ?\DateTime
    {
        return $this->last_login;
    }

    public function setLastLogin(?\DateTime $last_login): self
    {
        $this->last_login = $last_login;

        return $this;
    }
}
