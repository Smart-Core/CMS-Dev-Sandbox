<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Site\Entity;

use Doctrine\ORM\Mapping as ORM;
use SmartCore\RadBundle\Doctrine\ColumnTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Доступные модули на сайте
 *
 * @ORM\Entity()
 * @ORM\Table(name="modules",
 *      indexes={
 *          @ORM\Index(columns={"is_active"})
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"name", "developer"})
 *      }
 * )
 *
 * @UniqueEntity(fields={"bundle"}, message="Module this this bundle name is already installed.")
 */
#[ORM\Entity]
#[ORM\Table('modules')]
#[ORM\Index(columns: ['is_active'])]
class Module
{
    use ColumnTrait\Id;
    use ColumnTrait\IsActive;
    use ColumnTrait\CreatedAt;
    //use ColumnTrait\User;

    /**
     * @ORM\Column(type="string", length=190)
     */
    protected string $name;

    /**
     * @ORM\Column(type="string", length=190, nullable=false, unique=true)
     */
    protected string $bundle;

    protected array $info;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = trim($name);

        return $this;
    }

    public function getInfo(string $key = null): array
    {
        if ($key) {
            return $this->info[$key];
        }

        return $this->info;
    }

    public function setInfo(array $info): self
    {
        $this->info = $info;

        return $this;
    }

    public function addInfo(string $key, $val): self
    {
        $this->info[$key] = $val;

        return $this;
    }
}
