<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Site\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use SmartCore\RadBundle\Doctrine\ColumnTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table('regions')]
#[ORM\Index(columns: ['position'])]
#[UniqueEntity(fields: ['name'], message: 'Регион с таким именем уже используется')]
class Region
{
    use ColumnTrait\Id;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\Description;
    use ColumnTrait\Position;
    use ColumnTrait\UserId;

    #[ORM\Column(type: 'string', length: 50, nullable: false, unique: true)]
    #[Assert\NotBlank]
    protected string $name;

    /**
     * @var Folder[]|ArrayCollection
     */
    #[ORM\ManyToMany(targetEntity: Folder::class, inversedBy: 'regions', fetch: 'EXTRA_LAZY')]
    #[ORM\JoinTable('regions_inherit')]
    protected Collection $folders;

    #[ORM\OneToMany(targetEntity: Node::class, mappedBy: 'region', fetch: 'EXTRA_LAZY')]
    #[ORM\JoinTable('regions_inherit')]
    protected Collection $nodes;

    public function __construct(?string $name = null, ?string $description = null)
    {
        $this->created_at   = new \DateTimeImmutable();
        $this->folders      = new ArrayCollection();
        $this->description  = $description;
        $this->name         = $name;
        $this->position     = 0;
    }

    public function __toString(): string
    {
        $descr = $this->getDescription();

        return empty($descr) ? $this->getName() : $descr.' ('.$this->getName().')';
    }

    public function addFolder(Folder $folder): self
    {
        $this->folders->add($folder);

        return $this;
    }

    public function setFolders(Collection $folders): self
    {
        $this->folders = $folders;

        return $this;
    }

    /**
     * @return Folder[]|ArrayCollection
     */
    public function getFolders(): Collection
    {
        return $this->folders;
    }

    public function setName(string $name): self
    {
        if ('content' !== $this->name) {
            $this->name = $name;
        }

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
