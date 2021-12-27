<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\EntityCms;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use SmartCore\RadBundle\Doctrine\ColumnTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

// @todo Валидатор на уникальность 'domain', 'sub_path' учитывающий нули
#[ORM\Entity]
#[ORM\Table('sites')]
//#[ORM\UniqueConstraint(columns: ['domain_id', 'sub_path'])]
#[UniqueEntity('domain')]
#[UniqueEntity('sub_path')]
//#[UniqueEntity(fields: ['domain', 'sub_path'], ignoreNull: true)]
#[UniqueEntity(fields: ['name'], message: 'This site already exist')]
class Site
{
    const MULTILANGUAGE_MODE_DOMAIN = 'domain';
    const MULTILANGUAGE_MODE_PATH = 'path';
    const MULTILANGUAGE_MODE = [
        'off'    => 'Off (Single language)',
        'domain' => 'Domain (en.domain.com, ru.domain.com)',
        'path'   => 'Path (domain.com/en/, domain.com/ru/)',
    ];

    use ColumnTrait\Id;
    use ColumnTrait\IsEnabled;
    use ColumnTrait\NameUnique;
    use ColumnTrait\CreatedAt;

    #[ORM\Column(type: 'string', length: 6, options: ['default' => 'off'])]
    private string $multilanguage_mode;

    #[ORM\Column(type: 'string', nullable: true, length: 64)]
    private ?string $theme;

    // Подпуть сайта, например: domain.com/site1/
    #[ORM\Column(type: 'string', nullable: true, length: 32, unique: true)]
    private ?string $sub_path;

    // Если указан домен, то сайт будет открываться только по этому домену.
    // Только для MULTILANGUAGE_MODE_OFF
    #[ORM\ManyToOne(targetEntity: Domain::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: true, unique: true)]
    private ?Domain $domain;

    // Для MULTILANGUAGE_MODE_PATH and MULTILANGUAGE_MODE_OFF
    #[ORM\ManyToOne(targetEntity: Language::class, fetch: 'EXTRA_LAZY')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Language $default_language = null;

    /**
     * В режиме MULTILANGUAGE_MODE_PATH - включает список доступных языков для {_locale} и не требуется указывать домен.
     * В режиме MULTILANGUAGE_MODE_DOMAIN - обязательно нужно указать домен.
     *
     * @var SiteLanguage[]|ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: SiteLanguage::class, mappedBy: 'site', fetch: 'EXTRA_LAZY')]
    private Collection $languages;

    public function __construct(?string $name = null)
    {
        if (!empty($name)) {
            $this->name = $name;
        }

        $this->multilanguage_mode = self::MULTILANGUAGE_MODE['off'];
        $this->created_at         = new \DateTimeImmutable();
        $this->is_enabled         = true;
        $this->languages          = new ArrayCollection();
    }

    public function getMultilanguageModeValue(): string
    {
        return self::MULTILANGUAGE_MODE[$this->multilanguage_mode];
    }

    static public function getMultilanguageModeFormChoices(): array
    {
        return array_flip(self::MULTILANGUAGE_MODE);
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

    public function getMultilanguageMode(): string
    {
        return $this->multilanguage_mode;
    }

    public function setMultilanguageMode(string $multilanguage_mode): self
    {
        $this->multilanguage_mode = $multilanguage_mode;

        return $this;
    }

    public function getSubPath(): ?string
    {
        return $this->sub_path;
    }

    public function setSubPath(?string $sub_path): self
    {
        $this->sub_path = $sub_path;

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

    public function getDefaultLanguage(): ?Language
    {
        return $this->default_language;
    }

    public function setDefaultLanguage(?Language $default_language): self
    {
        $this->default_language = $default_language;

        return $this;
    }

    /**
     * @return SiteLanguage[]
     */
    public function getLanguages(): Collection
    {
        return $this->languages;
    }

    public function setLanguages(Collection $languages): self
    {
        $this->languages = $languages;

        return $this;
    }
}
