<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\EntityCms\Content;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use SmartCore\RadBundle\Doctrine\ColumnTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="content_dataset")
 *
 * @UniqueEntity(fields="name")
 * @UniqueEntity(fields="slug")
 */
class Dataset
{
    use ColumnTrait\Id;
    use ColumnTrait\NameUniqueNotBlank;
    use ColumnTrait\Description;
    use ColumnTrait\Slug32;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\UpdatedAt;
    use ColumnTrait\UserId;

    /**
     * @ORM\Column(type="string", length=100,  nullable=true)
     */
    protected ?string $icon;

    /**
     * @var Table[]|Collection
     *
     * @ORM\OneToMany(targetEntity="Table", mappedBy="dataset")
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected Collection $tables;

    public function __construct()
    {
        $this->created_at   = new \DateTimeImmutable();
        $this->description  = null;
        $this->icon         = null;
        $this->name         = '';
        $this->slug         = '';
        $this->tables       = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @param string|null $icon
     */
    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return Collection|Table[]
     */
    public function getTables(): Collection
    {
        return $this->tables;
    }

    /**
     * @param Collection|Table[] $tables
     */
    public function setTables(Collection $tables): self
    {
        $this->tables = $tables;

        return $this;
    }

}
