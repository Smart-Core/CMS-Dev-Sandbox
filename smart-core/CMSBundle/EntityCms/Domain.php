<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\EntityCms;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use SmartCore\RadBundle\Doctrine\ColumnTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[ORM\Table('domains')]
#[UniqueEntity(fields: ['name'], message: 'This domain already exist')]
class Domain
{
    use ColumnTrait\Id;
    use ColumnTrait\NameUnique;
    use ColumnTrait\Comment;
    use ColumnTrait\IsEnabled;
    use ColumnTrait\CreatedAt;

    // For Aliases
    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $is_redirect;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $paid_till_date;

    // For Aliases
    #[ORM\ManyToOne(targetEntity: Domain::class, inversedBy: 'children', fetch: 'EXTRA_LAZY')]
    private ?Domain $parent = null;

    /** @var Domain[] List of aliases */
    #[ORM\OneToMany(targetEntity: Domain::class, mappedBy: 'parent', fetch: 'EXTRA_LAZY')]
    #[ORM\OrderBy(['name' => 'ASC'])]
    private Collection $children;

    #[ORM\OneToMany(targetEntity: SiteLanguage::class, mappedBy: 'domain', cascade: ['remove'], fetch: 'EXTRA_LAZY')]
    private Collection $sites_languages;

    public function __construct(?string $name = null)
    {
        if (!empty($name)) {
            $this->name = $name;
        }

        $this->children    = new ArrayCollection();
        $this->created_at  = new \DateTimeImmutable();
        $this->is_enabled  = true;
        $this->is_redirect = false;
    }

    public function getParent(): ?Domain
    {
        return $this->parent;
    }

    public function setParent(?Domain $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function isIsRedirect(): bool
    {
        return $this->is_redirect;
    }

    public function setIsRedirect(bool $is_redirect): self
    {
        $this->is_redirect = $is_redirect;

        return $this;
    }

    /**
     * @return Collection|Domain[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function setChildren(Collection $children): self
    {
        $this->children = $children;

        return $this;
    }

    public function getPaidTillDate(): ?\DateTimeInterface
    {
        return $this->paid_till_date;
    }

    public function setPaidTillDate(?\DateTimeInterface $paid_till_date): self
    {
        $this->paid_till_date = $paid_till_date;

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
