<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Site\Model;

use Doctrine\ORM\Mapping as ORM;

trait UserGroupTrait
{
    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $is_default_folders_granted_read;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $is_default_folders_granted_write;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $is_default_nodes_granted_read;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $is_default_nodes_granted_write;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $is_default_regions_granted_read;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $is_default_regions_granted_write;

    public function isIsDefaultFoldersGrantedRead(): bool
    {
        return $this->is_default_folders_granted_read;
    }

    public function setIsDefaultFoldersGrantedRead(bool $is_default_folders_granted_read): self
    {
        $this->is_default_folders_granted_read = $is_default_folders_granted_read;

        return $this;
    }

    public function isIsDefaultFoldersGrantedWrite(): bool
    {
        return $this->is_default_folders_granted_write;
    }

    public function setIsDefaultFoldersGrantedWrite(bool $is_default_folders_granted_write): self
    {
        $this->is_default_folders_granted_write = $is_default_folders_granted_write;

        return $this;
    }

    public function isIsDefaultNodesGrantedRead(): bool
    {
        return $this->is_default_nodes_granted_read;
    }

    public function setIsDefaultNodesGrantedRead(bool $is_default_nodes_granted_read): self
    {
        $this->is_default_nodes_granted_read = $is_default_nodes_granted_read;

        return $this;
    }

    public function isIsDefaultNodesGrantedWrite(): bool
    {
        return $this->is_default_nodes_granted_write;
    }

    public function setIsDefaultNodesGrantedWrite(bool $is_default_nodes_granted_write): self
    {
        $this->is_default_nodes_granted_write = $is_default_nodes_granted_write;

        return $this;
    }

    public function isIsDefaultRegionsGrantedRead(): bool
    {
        return $this->is_default_regions_granted_read;
    }

    public function setIsDefaultRegionsGrantedRead(bool $is_default_regions_granted_read): self
    {
        $this->is_default_regions_granted_read = $is_default_regions_granted_read;

        return $this;
    }

    public function isIsDefaultRegionsGrantedWrite(): bool
    {
        return $this->is_default_regions_granted_write;
    }

    public function setIsDefaultRegionsGrantedWrite(bool $is_default_regions_granted_write): self
    {
        $this->is_default_regions_granted_write = $is_default_regions_granted_write;

        return $this;
    }
}
