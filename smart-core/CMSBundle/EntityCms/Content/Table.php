<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\EntityCms\Content;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use SmartCore\RadBundle\Doctrine\ColumnTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="content_tables",
 *      indexes={
 *          @ORM\Index(columns={"position"})
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"class_name", "dataset_id"}),
 *          @ORM\UniqueConstraint(columns={"name", "dataset_id"}),
 *          @ORM\UniqueConstraint(columns={"title", "dataset_id"}),
 *      }
 * )
 * @UniqueEntity(fields={"name", "dataset"})
 * @UniqueEntity(fields={"title", "dataset"})
 * @UniqueEntity(fields={"class_name", "dataset"})
 *
 * @todo repositoryClass
 * @todo кастомные валидаторы
 * @todo ORM\HasLifecycleCallbacks
 * @todo MappedSuperclass
 * @todo уникальности ORM\UniqueConstraint и UniqueEntity
 * @todo индексы, например: ORM\Index(columns={"site_id", "name"}),
 * @todo константы, в виде текстового поля, просто кастомный пхп код
 * @todo методы, в виде текстового поля, просто кастомный пхп код
 */
class Table
{
    use ColumnTrait\Id;
    use ColumnTrait\TitleNotBlank;
    use ColumnTrait\NameNotBlank;
    use ColumnTrait\Description;
    use ColumnTrait\Position;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\UpdatedAt;
    use ColumnTrait\UserId;

    /**
     * Используется для автоматического создания первичного ключа. Не маммится.
     */
    public ?string $primary_key_type = null;

    /**
     * @ORM\Column(type="string", length=190, nullable=false)
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    protected string $class_name;

    /**
     * @ORM\ManyToOne(targetEntity="Dataset", inversedBy="tables")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank()
     * @Assert\NotNull()
     */
    protected Dataset $dataset;

    /**
     * @ORM\OneToOne(targetEntity="Field")
     * @ORM\JoinColumn(nullable=true)
     */
    protected ?Field $primary_key;

    /**
     * @var Field[]|Collection
     *
     * @ORM\OneToMany(targetEntity="Field", mappedBy="table", cascade={"persist"})
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected Collection $fields;

    public function __construct()
    {
        $this->created_at   = new \DateTimeImmutable();
        $this->description  = null;
        $this->fields       = new ArrayCollection();
        $this->class_name   = '';
        $this->name         = '';
        $this->title        = '';
        $this->position     = 0;
        $this->primary_key  = null;
    }

    public function __toString(): string
    {
        return $this->title;
    }

    public function getDataset(): Dataset
    {
        return $this->dataset;
    }

    public function setDataset(Dataset $dataset): self
    {
        $this->dataset = $dataset;

        return $this;
    }

    public function getClassName(): string
    {
        return $this->class_name;
    }

    public function setClassName(?string $class_name): self
    {
        $this->class_name = trim((string) $class_name);

        return $this;
    }

    public function getFields(): Collection
    {
        return $this->fields;
    }

    public function setFields(Collection $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function getPrimaryKey(): ?Field
    {
        return $this->primary_key;
    }

    public function setPrimaryKey(Field $primary_key): self
    {
        $this->primary_key = $primary_key;

        return $this;
    }
}
