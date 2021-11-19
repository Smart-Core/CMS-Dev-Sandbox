<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\EntitySite;

use Doctrine\ORM\Mapping as ORM;
use SmartCore\RadBundle\Doctrine\ColumnTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="cms_sites")
 *
 * @UniqueEntity(fields={"name"}, message="Сайт с таким именем уже существует.")
 */
class Site
{
    use ColumnTrait\Id;
    use ColumnTrait\IsEnabled;
    use ColumnTrait\NameUnique;
    use ColumnTrait\CreatedAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $theme;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $web_root;

    /**
     * ORM\OneToOne(targetEntity="Domain")
     * ORM\JoinColumn(nullable=true)
     */
    protected ?Domain $domain;

    /**
     * ORM\ManyToOne(targetEntity="Language")
     * ORM\JoinColumn(nullable=true)
     */
    protected ?Language $language;

    public function __construct(?string $name = null)
    {
        if (!empty($name)) {
            $this->name = $name;
        }

        $this->created_at = new \DateTimeImmutable();
        $this->is_enabled = true;
        $this->position   = 0;
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

    public function getWebRoot(): ?string
    {
        return $this->web_root;
    }

    public function setWebRoot(?string $web_root): self
    {
        $this->web_root = $web_root;

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
