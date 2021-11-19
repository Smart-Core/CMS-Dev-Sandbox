<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\EntityStructure;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use SmartCore\RadBundle\Doctrine\ColumnTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="cms_folders",
 *      indexes={
 *          @ORM\Index(columns={"is_active"}),
 *          @ORM\Index(columns={"deleted_at"}),
 *          @ORM\Index(columns={"position"})
 *      },
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(columns={"parent_folder_id", "slug"}),
 *      }
 * )
 * @UniqueEntity(fields={"slug", "parent_folder"}, message="в каждой подпапке должен быть уникальный сегмент URI")
 * ORM\HasLifecycleCallbacks
 */
class Folder
{
    use ColumnTrait\Id;
    use ColumnTrait\IsActive;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\DeletedAt;
    use ColumnTrait\Description;
    use ColumnTrait\Position;
    use ColumnTrait\UserId;

    /**
     * @var Folder
     *
     * @ORM\ManyToOne(targetEntity="Folder", inversedBy="children", cascade={"persist"})
     * ORM\JoinColumn(name="folder_pid")
     */
    protected $parent_folder;

    /**
     * @var Folder[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Folder", mappedBy="parent_folder")
     * @ORM\OrderBy({"position" = "ASC"})
     */
    protected $children;

    /**
     * @var Node[]|ArrayCollection
     *
     * ORM\OneToMany(targetEntity="Node", mappedBy="folder")
     * ORM\OrderBy({"position" = "ASC"})
     */
    protected $nodes;

    /**
     * @var Region[]|ArrayCollection
     *
     * ORM\ManyToMany(targetEntity="Region", mappedBy="folders", fetch="EXTRA_LAZY")
     */
    protected $regions;

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
     * @var array
     *
     * @ORM\Column(type="array")
     */
    protected $permissions_cache;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=190, nullable=true)
     */
    protected $slug;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $is_file;

    /**
     * @var array
     *
     * @ORM\Column(type="array", nullable=true)
     */
    protected $meta;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $redirect_to;

    /**
     * @var string
     *
     * ORM\Column(type="integer", nullable=true)
     *
     * @todo можно сделать через связь
     */
    protected $router_node_id;

    /**
     * @var array
     *
     * @ORM\Column(type="array", nullable=true)
     */
    protected $lockout_nodes;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $template_inheritable;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $template_self;

    /**
     * Для отображения в формах. Не маппится в БД.
     */
    protected $form_title = '';


    public function __construct()
    {
        $this->groups_granted_read  = new ArrayCollection();
        $this->groups_granted_write = new ArrayCollection();
        $this->children             = new ArrayCollection();
        $this->created_at           = new \DateTime();
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
        $this->template_self        = null;
        $this->uri_part             = null;
    }

    /**
     * @return string
     */
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

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param bool $is_file
     *
     * @return $this
     */
    public function setIsFile(bool $is_file): self
    {
        $this->is_file = $is_file;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsFile(): bool
    {
        return $this->is_file;
    }

    /**
     * @return bool
     */
    public function isFile(): bool
    {
        return $this->is_file;
    }

    /**
     * @param string $uri_part
     *
     * @return $this
     */
    public function setUriPart($uri_part): self
    {
        $this->uri_part = $uri_part;

        return $this;
    }

    /**
     * @return string
     */
    public function getUriPart(): ?string
    {
        return (string) $this->uri_part;
    }

    /**
     * @param array $meta
     *
     * @return $this
     */
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

    /**
     * @return array
     */
    public function getMeta(): array
    {
        return empty($this->meta) ? [] : $this->meta;
    }

    /**
     * @param Folder $parent_folder
     *
     * @return $this
     */
    public function setParentFolder(Folder $parent_folder): Folder
    {
        $this->parent_folder = ($this->getId() == 1) ? null : $parent_folder;

        return $this;
    }

    /**
     * @return Folder|null
     */
    public function getParentFolder(): ?self
    {
        return $this->parent_folder;
    }

    /**
     * @param string $form_title
     *
     * @return $this
     */
    public function setFormTitle(string $form_title): self
    {
        $this->form_title = $form_title;

        return $this;
    }

    /**
     * @return string
     */
    public function getFormTitle(): string
    {
        return $this->form_title;
    }

    /**
     * @param int|null $router_node_id
     *
     * @return $this
     */
    public function setRouterNodeId($router_node_id): self
    {
        $this->router_node_id = empty($router_node_id) ? null : $router_node_id;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getRouterNodeId(): ?int
    {
        return $this->router_node_id;
    }

    /**
     * @param string $template_inheritable
     *
     * @return $this
     */
    public function setTemplateInheritable(?string $template_inheritable): self
    {
        $this->template_inheritable = $template_inheritable;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateInheritable(): ?string
    {
        return $this->template_inheritable;
    }

    /**
     * @param string $template_self
     *
     * @return $this
     */
    public function setTemplateSelf(?string $template_self): self
    {
        $this->template_self = $template_self;

        return $this;
    }

    /**
     * @return string
     */
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
     *
     * @return $this
     */
    public function setRegions($regions): self
    {
        $this->regions = $regions;

        return $this;
    }

    /**
     * @param UserGroup $userGroup
     *
     * @return Folder
     */
    public function addGroupGrantedRead(UserGroup $userGroup): self
    {
        if (!$this->groups_granted_read->contains($userGroup)) {
            $this->groups_granted_read->add($userGroup);
        }

        return $this;
    }

    /**
     * @return Folder
     */
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
     *
     * @return $this
     */
    public function setGroupsGrantedRead($groups_granted_read)
    {
        $this->groups_granted_read = $groups_granted_read;

        return $this;
    }

    /**
     * @param UserGroup $userGroup
     *
     * @return Folder
     */
    public function addGroupGrantedWrite(UserGroup $userGroup): Folder
    {
        if (!$this->groups_granted_write->contains($userGroup)) {
            $this->groups_granted_write->add($userGroup);
        }

        return $this;
    }

    /**
     * @return Folder
     */
    public function clearGroupGrantedWrite(): Folder
    {
        $this->groups_granted_write->clear();

        return $this;
    }


    /**
     * @return ArrayCollection|UserGroup[]
     */
    public function getGroupsGrantedWrite()
    {
        return $this->groups_granted_write;
    }

    /**
     * @param ArrayCollection|UserGroup[] $groups_granted_write
     *
     * @return $this
     */
    public function setGroupsGrantedWrite($groups_granted_write)
    {
        $this->groups_granted_write = $groups_granted_write;

        return $this;
    }

    /**
     * @return string
     */
    public function getRedirectTo(): ?string
    {
        return $this->redirect_to;
    }

    /**
     * @param string $redirect_to
     *
     * @return $this
     */
    public function setRedirectTo(?string $redirect_to)
    {
        $this->redirect_to = $redirect_to;

        return $this;
    }

    /**
     * @param array $permissions_cache
     *
     * @return $this
     */
    public function setPermissionsCache($permissions_cache)
    {
        $this->permissions_cache = $permissions_cache;

        return $this;
    }

    /**
     * @param string|null $permission
     *
     * @return array
     */
    public function getPermissionsCache(string $permission = null): array
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
