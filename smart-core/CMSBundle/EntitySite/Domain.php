<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\EntitySite;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use SmartCore\RadBundle\Doctrine\ColumnTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="cms_domains")
 *
 * @UniqueEntity(fields="name", message="Данный домен занят")
 */
class Domain
{
    use ColumnTrait\Id;
    use ColumnTrait\NameUnique;
    use ColumnTrait\Comment;
    use ColumnTrait\IsEnabled;
    use ColumnTrait\CreatedAt;

    /**
     * For Aliases
     *
     * @ORM\ManyToOne(targetEntity="Domain", inversedBy="children")
     * @ORM\JoinColumn(name="parent_pid")
     */
    protected ?Domain $parent;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected bool $is_redirect;

    /**
     * List of aliases
     *
     * @var Domain[]|Collection
     *
     * @ORM\OneToMany(targetEntity="Domain", mappedBy="parent")
     * @ORM\OrderBy({"position" = "ASC", "name" = "ASC"})
     */
    protected $children;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected ?\DateTimeInterface $paid_till_date;

    /**
     * @ORM\ManyToOne(targetEntity="Language", inversedBy="domains")
     * @ORM\JoinColumn(nullable=true)
     */
    protected ?Language $language;

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

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): self
    {
        $this->language = $language;

        return $this;
    }
}
