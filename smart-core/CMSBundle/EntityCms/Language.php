<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\EntityCms;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use SmartCore\RadBundle\Doctrine\ColumnTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="languages",
 *      indexes={
 *          @ORM\Index(columns={"is_enabled"})
 *      }
 * )
 *
 * @UniqueEntity(fields={"name"}, message="Язык с таким именем уже существует.")
 * @UniqueEntity(fields={"code"}, message="Язык с таким кодом уже существует.")
 */
class Language
{
    use ColumnTrait\Id;
    use ColumnTrait\IsEnabled;
    use ColumnTrait\NameUnique;
    use ColumnTrait\CreatedAt;

    /**
     * @ORM\Column(type="string", unique=true, length=12)
     */
    protected string $code;

    /**
     * @var Domain[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Domain", mappedBy="language")
     */
    protected Collection $domains;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->code       = '';
        $this->domains    = new ArrayCollection();
        $this->is_enabled = true;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection|Domain[]
     */
    public function getDomains(): Collection
    {
        return $this->domains;
    }

    public function setDomains(Collection $domains): self
    {
        $this->domains = $domains;

        return $this;
    }
}
