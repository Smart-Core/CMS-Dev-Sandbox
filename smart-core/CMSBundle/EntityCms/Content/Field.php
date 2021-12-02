<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\EntityCms\Content;

use Doctrine\ORM\Mapping as ORM;
use SmartCore\RadBundle\Doctrine\ColumnTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="content_fields",
 *      indexes={
 *          @ORM\Index(columns={"position"})
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"name", "table_id"}),
 *          @ORM\UniqueConstraint(columns={"title", "table_id"}),
 *      }
 * )
 * @UniqueEntity(fields={"name", "table"})
 * @UniqueEntity(fields={"title", "table"})
 */
class Field
{
    use ColumnTrait\Id;
    use ColumnTrait\TitleNotBlank;
    use ColumnTrait\NameNotBlank;
    use ColumnTrait\Comment;
    use ColumnTrait\Position;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\UpdatedAt;
    use ColumnTrait\UserId;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default":0})
     */
    protected bool $is_nullable;

    /**
     * Enum({"PRIMARY", "INDEX", "UNIQUE", "FULLTEXT"})
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected ?string $index;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $default_value;

    /**
     * @ORM\Column(type="string", length=190, nullable=true)
     */
    protected ?string $length;

    /**
     * @ORM\Column(type="string", length=190, nullable=true)
     */
    protected ?string $trait;

    /**
     * @ORM\Column(type="string", length=20, nullable=false)
     */
    protected string $type;

    /**
     * @ORM\ManyToOne(targetEntity="Table", inversedBy="fields", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    protected Table $table;

    public function __construct()
    {
        $this->created_at   = new \DateTimeImmutable();
        $this->default_value = null;
        $this->description  = null;
        $this->name         = '';
        $this->index        = null;
        $this->is_nullable  = false;
        $this->length       = null;
        $this->title        = '';
        $this->type         = 'string';
        $this->position     = 0;
    }

    public function __toString(): string
    {
        return $this->title;
    }

    public function getTable(): Table
    {
        return $this->table;
    }

    public function setTable(Table $table): self
    {
        $this->table = $table;

        return $this;
    }

    public function getIndex(): ?string
    {
        return $this->index;
    }

    public function setIndex(?string $index): self
    {
        $this->index = $index;

        return $this;
    }

    public function isIsNullable(): bool
    {
        return $this->is_nullable;
    }

    public function setIsNullable(bool $is_nullable): self
    {
        $this->is_nullable = $is_nullable;

        return $this;
    }

    public function getTrait(): ?string
    {
        return $this->trait;
    }

    public function setTrait(?string $trait): self
    {
        $this->trait = $trait;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDefaultValue(): ?string
    {
        return $this->default_value;
    }

    public function setDefaultValue(?string $default_value): self
    {
        $this->default_value = $default_value;

        return $this;
    }

    public function getLength(): ?string
    {
        return $this->length;
    }

    public function setLength(?string $length): self
    {
        $this->length = $length;

        return $this;
    }
}
