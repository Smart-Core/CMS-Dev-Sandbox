<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Site\Entity;

use Doctrine\ORM\Mapping as ORM;
use SmartCore\CMSBundle\Site\Repository\NodeRepository;
use SmartCore\RadBundle\Doctrine\ColumnTrait;
use SmartCore\Bundle\CMSBundle\Tools\FrontControl;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NodeRepository::class)]
#[ORM\Table('nodes')]
#[ORM\Index(columns: ['is_active'])]
#[ORM\Index(columns: ['deleted_at'])]
#[ORM\Index(columns: ['position'])]
#[ORM\Index(columns: ['region_id'])]
#[ORM\HasLifecycleCallbacks]
class Node implements \Serializable // NodeInterface,
{
    // Получать элементы управления для тулбара.
    const TOOLBAR_NO                    = 0; // Никогда
    const TOOLBAR_ONLY_IN_SELF_FOLDER   = 1; // Только в собственной папке
    const TOOLBAR_ALWAYS                = 2; // Всегда

    use ColumnTrait\Id;
    use ColumnTrait\IsActive;
    use ColumnTrait\CreatedAt;
    use ColumnTrait\DeletedAt;
    use ColumnTrait\Description;
    use ColumnTrait\Position;
    use ColumnTrait\UserId;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint")
     */
    protected $controls_in_toolbar;

    /**
     * @ORM\Column(type="sqlite_json", nullable=false)
     */
    protected array $params;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected ?string $template = null;

    /**
     * Приоритет порядка выполнения.
     *
     * @ORM\Column(type="smallint")
     */
    protected int $priority;

    /**
     * Может ли нода кешироваться.
     *
     * @ORM\Column(type="boolean")
     */
    protected bool $is_cached;

    /**
     * Использовать Edit-In-Place. Если отключить также не будет генерироваться div вокруг ноды.
     *
     * @ORM\Column(type="boolean", options={"default":1})
     */
    protected bool $is_use_eip = true;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $code_before = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $code_after = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    //protected $cache_params;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    //protected $plugins;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    //protected $permissions;

    #[ORM\ManyToOne(targetEntity: Module::class, inversedBy: 'nodes', fetch: 'EXTRA_LAZY', cascade: ['persist'])]
    #[Assert\NotBlank]
    protected Module $module;

    #[ORM\ManyToOne(targetEntity: Folder::class, inversedBy: 'nodes', fetch: 'EXTRA_LAZY', cascade: ['persist'])]
    #[Assert\NotBlank]
    protected Folder $folder;

    #[ORM\ManyToOne(targetEntity: Region::class, inversedBy: 'nodes', fetch: 'EAGER', cascade: ['persist'])]
    #[Assert\NotBlank]
    protected Region $region;

    // ================================= Unmapped properties =================================

    /**
     * Хранение folder_id для минимизации кол-ва запросов.
     */
    protected ?int $folder_id = null;

    protected array $controller = [];

    /**
     * Edit-In-Place.
     */
    protected bool $eip = false;

    /**
     * @var FrontControl[]
     */
    protected array $front_controls = [];

    protected ?string $region_name = null;

    public function __construct()
    {
        $this->controls_in_toolbar = self::TOOLBAR_ONLY_IN_SELF_FOLDER;
        $this->created_at   = new \DateTimeImmutable();
        $this->is_active    = true;
        $this->is_cached    = false;
        $this->params       = [];
        $this->position     = 0;
        $this->priority     = 0;
    }

    public function serialize(): string
    {
        $this->getFolderId(); // Lazy load

        return serialize([
            //return igbinary_serialize([
            $this->id,
            $this->is_active,
            $this->is_cached,
            $this->is_use_eip,
            $this->module,
            $this->params,
            $this->code_before,
            $this->code_after,
            $this->folder,
            $this->folder_id,
            $this->region,
            $this->region_name,
            $this->position,
            $this->priority,
            $this->template,
            $this->description,
            $this->controls_in_toolbar,
            $this->user,
            $this->created_at,
            $this->deleted_at,
            $this->controller,
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        [
            $this->id,
            $this->is_active,
            $this->is_cached,
            $this->is_use_eip,
            $this->module,
            $this->params,
            $this->code_before,
            $this->code_after,
            $this->folder,
            $this->folder_id,
            $this->region,
            $this->region_name,
            $this->position,
            $this->priority,
            $this->template,
            $this->description,
            $this->controls_in_toolbar,
            $this->user,
            $this->created_at,
            $this->deleted_at,
            $this->controller,
        ] = unserialize($serialized, ['allowed_classes' => false]);
        //) = igbinary_unserialize($serialized);
    }

    public function getCodeBefore(): ?string
    {
        return $this->code_before;
    }

    public function setCodeBefore(?string $code_before): self
    {
        $this->code_before = $code_before;

        return $this;
    }

    public function getCodeAfter(): ?string
    {
        return $this->code_after;
    }

    public function setCodeAfter(?string $code_after): self
    {
        $this->code_after = $code_after;

        return $this;
    }

    /**
     * @param int $controls_in_toolbar
     *
     * @return $this
     */
    public function setControlsInToolbar($controls_in_toolbar)
    {
        $this->controls_in_toolbar = $controls_in_toolbar;

        return $this;
    }

    /**
     * @return int
     */
    public function getControlsInToolbar()
    {
        return $this->controls_in_toolbar;
    }

    public function setIsCached($is_cached): self
    {
        $this->is_cached = $is_cached;

        return $this;
    }

    public function getIsCached(): bool
    {
        return $this->is_cached;
    }

    public function setRegion(Region $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getRegion(): Region
    {
        return $this->region;
    }

    public function getRegionName(): string
    {
        if (null === $this->region_name) {
            $this->region_name = $this->getRegion()->getName();
        }

        return $this->region_name;
    }

    public function setFolder(Folder $folder): self
    {
        $this->folder = $folder;

        return $this;
    }

    public function getFolder(): Folder
    {
        return $this->folder;
    }

    public function getModule(): Module
    {
        return $this->module;
    }

    public function setModule(Module $module): self
    {
        $this->module = $module;

        return $this;
    }

    public function setParams(array $params): self
    {
        $this->params = $params;

        return $this;
    }

    public function getParams(): array
    {
        return (empty($this->params)) ? [] : $this->params;
    }

    public function getParam(string $key, $default = null): mixed
    {
        return (isset($this->params[$key])) ? $this->params[$key] : $default;
    }

    public function setPriority(int $priority): self
    {
        if (empty($priority)) {
            $priority = 0;
        }

        $this->priority = $priority;

        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setTemplate(?string $template = null): self
    {
        $this->template = $template;

        return $this;
    }

    public function getTemplate($default = null): string
    {
        return empty($this->template) ? $default : $this->template;
    }

    public function getFolderId(): int
    {
        if ($this->folder_id == null) {
            $this->folder_id = $this->getFolder()->getId();
        }

        return $this->folder_id;
    }

    public function setEip(bool $eip): self
    {
        $this->eip = $eip;

        return $this;
    }

    /**
     * @return bool
     */
    public function getEip()
    {
        return $this->eip;
    }

    public function isEip(): bool
    {
        return $this->eip;
    }

    public function getIsUseEip(): bool
    {
        return $this->is_use_eip;
    }

    public function setIsUseEip(bool $is_use_eip): self
    {
        $this->is_use_eip = $is_use_eip;

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function addFrontControl(string $name): FrontControl
    {
        if (isset($this->front_controls[$name])) {
            throw new \Exception("Front control: '{$name}' already exists.");
        }

        $this->front_controls[$name] = new FrontControl();
        $this->front_controls[$name]->setDescription($this->getDescription());

        return $this->front_controls[$name];
    }

    /**
     * @return FrontControl[]
     */
    public function getFrontControls(): array
    {
        $data = [];

        if ($this->isEip() and $this->getIsUseEip()) {
            foreach ($this->front_controls as $name => $control) {
                $data[$name] = $control->getData();
            }
        }

        return $data;
    }

    public function setController(string $controller): self
    {
        $this->controller = $controller;

        return $this;
    }

    public function getControllerParams(): array
    {
        $params = [];
        foreach ($this->controller as $key => $val) {
            if ($key !== '_controller' and $key !== '_route') {
                $params[$key] = $val;
            }
        }

        return $params;
    }

    /**
     * @todo Продумать где подменять action у нод.
     */
    public function getController($controllerName = null, $actionName = 'index'): array
    {
        if (null !== $controllerName or 'index' !== $actionName) {
            $className = empty($controllerName) ? $this->module : $controllerName;

            return [
                '_controller' => $this->module.'ModuleBundle:'.$className.':'.$actionName,
            ];
        }

        if (empty($this->controller)) {
            $className = (null === $controllerName) ? $this->module : $controllerName;
            $this->controller['_controller'] = $this->module.'ModuleBundle:'.$className.':'.$actionName;
        }

        return $this->controller;
    }
}
