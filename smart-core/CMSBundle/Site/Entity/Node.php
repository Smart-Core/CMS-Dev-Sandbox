<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Site\Entity;

use Doctrine\ORM\Mapping as ORM;
//use SmartCore\RadBundle\CMS\NodeInterface;
use SmartCore\RadBundle\Doctrine\ColumnTrait;
use SmartCore\Bundle\CMSBundle\Tools\FrontControl;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="SmartCore\CMSBundle\Site\Repository\NodeRepository")*
 * @ORM\Table(name="nodes",
 *      indexes={
 *          @ORM\Index(columns={"is_active"}),
 *          @ORM\Index(columns={"deleted_at"}),
 *          @ORM\Index(columns={"position"}),
 *          @ORM\Index(columns={"region_id"}),
 *          @ORM\Index(columns={"module"})
 *      }
 * )
 * @ORM\HasLifecycleCallbacks
 */
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
     * @var string
     *
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank()
     */
    protected $module;

    /**
     * @var
     *
     * @ORM\Column(type="sqlite_json", nullable=false)
     */
    protected array $params;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $template;

    /**
     * @var Folder
     *
     * @ORM\ManyToOne(targetEntity="Folder", inversedBy="nodes")
     * @Assert\NotBlank()
     */
    protected $folder;

    /**
     * @var Region
     *
     * @ORM\ManyToOne(targetEntity="Region", fetch="EAGER")
     * @Assert\NotBlank()
     */
    protected $region;

    /**
     * Приоритет порядка выполнения.
     *
     * @var int
     *
     * @ORM\Column(type="smallint")
     */
    protected $priority;

    /**
     * Может ли нода кешироваться.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $is_cached;

    /**
     * Использовать Edit-In-Place. Если отключить также не будет генерироваться div вокруг ноды.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default":1})
     */
    protected $is_use_eip;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $code_before;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $code_after;

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

    // ================================= Unmapped properties =================================

    /**
     * Хранение folder_id для минимизации кол-ва запросов.
     *
     * @var int|null
     */
    protected $folder_id = null;

    /**
     * @var array
     */
    protected $controller = [];

    /**
     * Edit-In-Place.
     *
     * @var bool
     */
    protected $eip = false;

    /**
     * @var FrontControl[]
     */
    protected $front_controls = [];

    protected ?string $region_name = null;

    public function __construct()
    {
        $this->controls_in_toolbar = self::TOOLBAR_ONLY_IN_SELF_FOLDER;
        $this->created_at   = new \DateTimeImmutable();
        $this->is_active    = true;
        $this->is_cached    = false;
        $this->is_use_eip   = true;
        $this->params       = [];
        $this->position     = 0;
        $this->priority     = 0;
    }

    /**
     * Сериализация.
     */
    public function serialize()
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
        list(
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
            $this->controller) = unserialize($serialized);
        //) = igbinary_unserialize($serialized);
    }

    /**
     * @return string|null
     */
    public function getCodeBefore()
    {
        return $this->code_before;
    }

    /**
     * @param string $code_before
     *
     * @return $this
     */
    public function setCodeBefore($code_before)
    {
        $this->code_before = $code_before;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCodeAfter()
    {
        return $this->code_after;
    }

    /**
     * @param string $code_after
     *
     * @return $this
     */
    public function setCodeAfter($code_after)
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

    /**
     * @param bool $is_cached
     *
     * @return $this
     */
    public function setIsCached($is_cached)
    {
        $this->is_cached = $is_cached;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsCached()
    {
        return $this->is_cached;
    }

    /**
     * @param Region $region
     *
     * @return $this
     */
    public function setRegion(Region $region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return Region
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @return string
     */
    public function getRegionName()
    {
        if (null === $this->region_name) {
            $this->region_name = $this->getRegion()->getName();
        }

        return $this->region_name;
    }

    /**
     * @param Folder $folder
     *
     * @return $this
     */
    public function setFolder(Folder $folder)
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * @return Folder
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * @param string $module
     *
     * @return $this
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * @return string
     */
    public function getModule()
    {
        return $this->module;
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
