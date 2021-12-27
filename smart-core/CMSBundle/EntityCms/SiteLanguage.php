<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\EntityCms;

use Doctrine\ORM\Mapping as ORM;
use SmartCore\RadBundle\Doctrine\ColumnTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ORM\Table('sites_languages')]
#[UniqueEntity(fields: ['site', 'language'], errorPath: 'language')]
#[UniqueEntity('domain')]
class SiteLanguage
{
    use ColumnTrait\Id;
    use ColumnTrait\CreatedAt;

    // Тема языка, приоритетнее темы сайта.
    #[ORM\Column(type: 'string', nullable: true, length: 64)]
    private ?string $theme = null;

    #[ORM\ManyToOne(targetEntity: Site::class, inversedBy: 'languages', fetch: 'EXTRA_LAZY')]
    private Site $site;

    #[ORM\ManyToOne(targetEntity: Domain::class, inversedBy: 'sites_languages', fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: true, unique: true)]
    private ?Domain $domain;

    #[ORM\ManyToOne(targetEntity: Language::class, inversedBy: 'sites_languages', fetch: 'EXTRA_LAZY')]
    private Language $language;

    public function __construct(?Site $site = null)
    {
        $this->created_at = new \DateTimeImmutable();

        if ($site) {
            $this->site = $site;
        }
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setTheme(?string $theme): self
    {
        $this->theme = $theme;

        return $this;
    }

    public function getSite(): Site
    {
        return $this->site;
    }

    public function setSite(Site $site): self
    {
        $this->site = $site;

        return $this;
    }

    public function getDomain(): ?Domain
    {
        return $this->domain;
    }

    public function setDomain(?Domain $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function setLanguage(Language $language): self
    {
        $this->language = $language;

        return $this;
    }
}
