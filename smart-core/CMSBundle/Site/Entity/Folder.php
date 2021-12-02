<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Site\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use SmartCore\CMSBundle\Site\Repository\FolderRepository;
use SmartCore\RadBundle\Doctrine\ColumnTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ORM\HasLifecycleCallbacks
 */
#[ORM\Entity(repositoryClass: FolderRepository::class)]
#[ORM\Table('folders')]
#[ORM\Index(columns: ['is_active'])]
#[ORM\Index(columns: ['deleted_at'])]
#[ORM\Index(columns: ['position'])]
#[ORM\UniqueConstraint(columns: ['slug', 'parent_folder_id'])]
#[UniqueEntity(fields: ['slug', 'parent_folder'], message: 'в каждой подпапке должен быть уникальный сегмент URI')]
class Folder
{
    use ColumnTrait\Id;
    use ColumnTrait\IsActive;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\DeletedAt;
    use ColumnTrait\Description;
    use ColumnTrait\Position;
    use ColumnTrait\Slug128;
    use ColumnTrait\TitleNotBlank;
    use ColumnTrait\UserId;

    /**
     * @ORM\Column(type="array")
     */
    protected array $permissions_cache = [];

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $is_file;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    protected ?array $meta = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $redirect_to = null;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    protected ?array $lockout_nodes = null;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected ?string $template_inheritable = null;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected ?string $template_self = null;

    #[ORM\ManyToOne(targetEntity: Folder::class, inversedBy: 'children', fetch: 'EXTRA_LAZY', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true)]
    protected ?Folder $parent_folder = null;

    /** @var Folder[]|ArrayCollection */
    #[ORM\OneToMany(targetEntity: Folder::class, mappedBy: 'parent_folder', fetch: 'EXTRA_LAZY')]
    #[ORM\OrderBy(['position' => 'ASC'])]
    protected Collection $children;

    /** @var Node[]|ArrayCollection */
    #[ORM\OneToMany(targetEntity: Node::class, mappedBy: 'folder', fetch: 'EXTRA_LAZY')]
    #[ORM\OrderBy(['position' => 'ASC'])]
    protected Collection $nodes;

    /** @var Region[]|ArrayCollection */
    #[ORM\ManyToMany(targetEntity: Region::class, mappedBy: 'folders', fetch: 'EXTRA_LAZY')]
    protected Collection $regions;

    /**
     * @var UserGroup[]|ArrayCollection
     *
     * ORM\ManyToMany(targetEntity="UserGroup", inversedBy="folders_granted_read", fetch="EXTRA_LAZY")
     * ORM\JoinTable(name="cms_permissions_folders_read")
     * ORM\OrderBy({"position" = "ASC", "title" = "ASC"})
     */
    protected $groups_granted_read;

    /**
     * @var UserGroup[]|ArrayCollection
     *
     * ORM\ManyToMany(targetEntity="UserGroup", inversedBy="folders_granted_write", fetch="EXTRA_LAZY")
     * ORM\JoinTable(name="cms_permissions_folders_write")
     * ORM\OrderBy({"position" = "ASC", "title" = "ASC"})
     */
    protected $groups_granted_write;

    /**
     * @ORM\ManyToOne(targetEntity="Node")
     * @ORM\Column(nullable=true)
     */
    protected ?Node $router_node = null;

    /**
     * Для отображения в формах. Не маппится в БД.
     */
    protected string $form_title = '';

    public function __construct()
    {
        $this->groups_granted_read  = new ArrayCollection();
        $this->groups_granted_write = new ArrayCollection();
        $this->children             = new ArrayCollection();
        $this->created_at           = new \DateTimeImmutable();
        $this->is_active            = true;
        $this->is_file              = false;
        $this->lockout_nodes        = null;
        $this->meta                 = [];
        $this->nodes                = new ArrayCollection();
        $this->parent_folder        = null;
        $this->permissions_cache    = [];
        $this->position             = 0;
        $this->regions              = new ArrayCollection();
        $this->redirect_to          = null;
        $this->router_node_id       = null;
        $this->template_inheritable = null;
        $this->slug                 = '';
    }

    public function __toString(): string
    {
        return $this->getTitle();
    }

    /**
     * @ORM\PreFlush()
     */
    public function updatePermissionsCache()
    {
        $this->permissions_cache = [];

        foreach ($this->getGroupsGrantedRead() as $group) {
            $this->permissions_cache['read'][$group->getId()] = $group->getName();
        }

        foreach ($this->getGroupsGrantedWrite() as $group) {
            $this->permissions_cache['write'][$group->getId()] = $group->getName();
        }
    }

    /**
     * @return Folder[]|ArrayCollection
     */
    public function getChildren(): ?Collection
    {
        return $this->children;
    }

    /**
     * @return Node[]|ArrayCollection
     */
    public function getNodes(): ?Collection
    {
        return $this->nodes;
    }

    public function setIsFile(bool $is_file): self
    {
        $this->is_file = $is_file;

        return $this;
    }

    public function getIsFile(): bool
    {
        return $this->is_file;
    }

    public function isFile(): bool
    {
        return $this->is_file;
    }

    public function setUriPart(?string $uri_part): self
    {
        $this->uri_part = $uri_part;

        return $this;
    }

    public function getUriPart(): string
    {
        return (string) $this->uri_part;
    }

    public function setMeta(array $meta): self
    {
        foreach ($meta as $name => $value) {
            if (empty($value)) {
                unset($meta[$name]);
            }
        }

        $this->meta = $meta;

        return $this;
    }

    public function getMeta(): array
    {
        return empty($this->meta) ? [] : $this->meta;
    }

    public function setParentFolder(Folder $parent_folder): self
    {
        $this->parent_folder = ($this->getId() == 1) ? null : $parent_folder;

        return $this;
    }

    public function getParentFolder(): ?self
    {
        return $this->parent_folder;
    }

    public function setFormTitle(string $form_title): self
    {
        $this->form_title = $form_title;

        return $this;
    }

    public function getFormTitle(): string
    {
        return $this->form_title;
    }

    public function setRouterNodeId(?int $router_node_id): self
    {
        $this->router_node_id = empty($router_node_id) ? null : $router_node_id;

        return $this;
    }

    public function getRouterNodeId(): ?int
    {
        return $this->router_node_id;
    }

    public function setTemplateInheritable(?string $template_inheritable): self
    {
        $this->template_inheritable = $template_inheritable;

        return $this;
    }

    public function getTemplateInheritable(): ?string
    {
        return $this->template_inheritable;
    }

    public function setTemplateSelf(?string $template_self): self
    {
        $this->template_self = $template_self;

        return $this;
    }

    public function getTemplateSelf(): ?string
    {
        return $this->template_self;
    }

    /**
     * @return Region[]|ArrayCollection
     */
    public function getRegions(): ?Collection
    {
        return $this->regions;
    }

    /**
     * @param Region[]|ArrayCollection $regions
     */
    public function setRegions(Collection $regions): self
    {
        $this->regions = $regions;

        return $this;
    }

    public function addGroupGrantedRead(UserGroup $userGroup): self
    {
        if (!$this->groups_granted_read->contains($userGroup)) {
            $this->groups_granted_read->add($userGroup);
        }

        return $this;
    }

    public function clearGroupGrantedRead(): self
    {
        $this->groups_granted_read->clear();

        return $this;
    }

    /**
     * @return UserGroup[]|ArrayCollection
     */
    public function getGroupsGrantedRead(): Collection
    {
        return $this->groups_granted_read;
    }

    /**
     * @param ArrayCollection|UserGroup[] $groups_granted_read
     */
    public function setGroupsGrantedRead(Collection $groups_granted_read): self
    {
        $this->groups_granted_read = $groups_granted_read;

        return $this;
    }

    public function addGroupGrantedWrite(UserGroup $userGroup): self
    {
        if (!$this->groups_granted_write->contains($userGroup)) {
            $this->groups_granted_write->add($userGroup);
        }

        return $this;
    }

    public function clearGroupGrantedWrite(): self
    {
        $this->groups_granted_write->clear();

        return $this;
    }

    /**
     * @return ArrayCollection|UserGroup[]
     */
    public function getGroupsGrantedWrite(): Collection
    {
        return $this->groups_granted_write;
    }

    /**
     * @param ArrayCollection|UserGroup[] $groups_granted_write
     */
    public function setGroupsGrantedWrite($groups_granted_write): self
    {
        $this->groups_granted_write = $groups_granted_write;

        return $this;
    }

    public function getRedirectTo(): ?string
    {
        return $this->redirect_to;
    }

    public function setRedirectTo(?string $redirect_to): self
    {
        $this->redirect_to = $redirect_to;

        return $this;
    }

    public function setPermissionsCache(array $permissions_cache): self
    {
        $this->permissions_cache = $permissions_cache;

        return $this;
    }

    public function getPermissionsCache(?string $permission = null): array
    {
        if (!empty($permission)) {
            if (isset($this->permissions_cache[$permission])) {
                return $this->permissions_cache[$permission];
            } else {
                return [];
            }
        }

        if (empty($this->permissions_cache)) {
            $this->permissions_cache = [];
        }

        return $this->permissions_cache;
    }
}
