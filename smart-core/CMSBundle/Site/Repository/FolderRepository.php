<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Site\Repository;

use Doctrine\ORM\EntityRepository;
use SmartCore\CMSBundle\Site\Entity\Folder;
use SmartCore\RadBundle\Doctrine\RepositoryTrait;

class FolderRepository extends EntityRepository
{
    use RepositoryTrait\FindDeleted;

    /**
     * @return Folder[]
     */
    public function findByParent(?Folder $parent_folder = null): array
    {
        return $this->findBy(['parent_folder' => $parent_folder]);
    }
}
