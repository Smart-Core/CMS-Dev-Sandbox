<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Site\Form\Tree;

use Doctrine\Persistence\ManagerRegistry;
use SmartCore\CMSBundle\Site\Entity\Folder;
use SmartCore\CMSBundle\Site\Repository\FolderRepository;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;

class FolderLoader implements EntityLoaderInterface
{
    private FolderRepository $repository;
    protected array $result;
    protected int $level;
    private bool $only_active = false;

    public function __construct(ManagerRegistry $doctrine, string $emName)
    {
        $this->repository = $doctrine->getManager($emName)->getRepository(Folder::class);
    }

    public function getEntities()
    {
        $this->result = [];
        $this->level = 0;

        $this->addChild();

        return $this->result;
    }

    public function getEntitiesByIds(string $identifier, array $values)
    {
        return $this->repository->findBy(
            [$identifier => $values]
        );
    }

    protected function addChild(?Folder $parent_folder = null)
    {
        $level = $this->level;
        $ident = '';
        while ($level--) {
            $ident .= '&nbsp;&nbsp;';
//            $ident .= '    ';
        }

        $this->level++;

        $criteria = ['parent_folder' => $parent_folder];

        if ($this->only_active) {
            $criteria['is_active'] = true;
        }

        $folders = $this->repository->findBy($criteria, ['position' => 'ASC']);

        /** @var $folder Folder */
        foreach ($folders as $folder) {
            $folder->setFormTitle($ident.$folder->getTitle());
            $this->result[] = $folder;
            $this->addChild($folder);
        }

        $this->level--;
    }

    public function setOnlyActive(bool $only_active): self
    {
        $this->only_active = $only_active;

        return $this;
    }
}
