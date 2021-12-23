<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Site\Form\Tree;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use SmartCore\CMSBundle\Site\Entity\Folder;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;
use Symfony\Bridge\Doctrine\Form\Type\DoctrineType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FolderTreeType extends DoctrineType
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'choice_label'  => 'form_title',
            'class'         => Folder::class,
//            'choice_loader' => $choiceLoader,
//            'only_active'   => false,
        ]);

//        dump($resolver);
    }

    public function getLoader(ObjectManager $manager, object $queryBuilder, string $class)
    {
        return new FolderLoader($this->registry, 'site_1'); // @todo !!!
    }

    public function getBlockPrefix()
    {
        return 'cms_folder_tree';
    }
}
