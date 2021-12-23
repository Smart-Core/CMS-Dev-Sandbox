<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\EntityCms;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use SmartCore\RadBundle\Doctrine\ColumnTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table('languages')]
#[ORM\Index(columns: ['is_enabled'])]
#[UniqueEntity(fields: ['name'], message: 'Language with this name already exist')]
#[UniqueEntity(fields: ['code'], message: 'Language with this code already exist')]
class Language
{
    use ColumnTrait\Id;
    use ColumnTrait\IsEnabled;
    use ColumnTrait\NameUnique;
    use ColumnTrait\Position;
    use ColumnTrait\CreatedAt;

    #[ORM\Column(type: 'string', length: 12, unique: true)]
    #[Assert\Length(min: 2, max: 12)]
    #[Assert\NotBlank]
    protected string $code;

    /** @var Domain[]|ArrayCollection */
    #[ORM\OneToMany(targetEntity: Domain::class, mappedBy: 'language', fetch: 'EXTRA_LAZY')]
    protected Collection $domains;

    #[ORM\ManyToMany(targetEntity: Site::class, mappedBy: 'languages', fetch: 'EXTRA_LAZY')]
    protected Collection $sites;

    public function __construct(string $code = '')
    {
        $this->created_at = new \DateTimeImmutable();
        $this->code       = $code;
        $this->name       = $code;
        $this->domains    = new ArrayCollection();
        $this->is_enabled = true;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = trim((string) $code);

        return $this;
    }

    /**
     * @return Domain[]
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

    /**
     * @return Site[]
     */
    public function getSites(): Collection
    {
        return $this->sites;
    }

    public function setSites(Collection $sites): self
    {
        $this->sites = $sites;

        return $this;
    }
}
