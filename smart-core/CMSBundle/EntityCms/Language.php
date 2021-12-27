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
    private string $code;

    #[ORM\OneToMany(targetEntity: SiteLanguage::class, mappedBy: 'language', fetch: 'EXTRA_LAZY')]
    private Collection $sites_languages;

    public function __construct(string $code = '')
    {
        $this->created_at       = new \DateTimeImmutable();
        $this->code             = $code;
        $this->name             = $code;
        $this->sites_languages  = new ArrayCollection();
        $this->is_enabled       = true;
    }

    public function __toString(): string
    {
        return $this->getName() . ' (' . $this->code . ')';
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
     * @return SiteLanguage[]
     */
    public function getSitesLanguages(): Collection
    {
        return $this->sites_languages;
    }

    public function setSitesLanguages(Collection $sites_languages): self
    {
        $this->sites_languages = $sites_languages;

        return $this;
    }
}
