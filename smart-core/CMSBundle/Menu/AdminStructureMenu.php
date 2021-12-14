<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Menu;

use Doctrine\ORM\EntityManagerInterface;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use SmartCore\CMSBundle\Site\Entity\Folder;

class AdminStructureMenu
{
    public function __construct(
        private FactoryInterface $factory,
        private EntityManagerInterface $em,
    ) {}

    public function menu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('admin_structure');

        $menu->setExtra('select_intehitance', false);
        $menu->setChildrenAttribute('class', isset($options['class']) ? $options['class'] : 'nav nav-tabs');
        $menu->addChild('Site structure', ['route' => 'cms_admin.structure']);
        $menu->addChild('Create folder',  ['route' => 'cms_admin.structure_folder_create']);
        $menu->addChild('Connect module', ['route' => 'cms_admin.structure_node_create']);
        $menu->addChild('Regions',        ['route' => 'cms_admin.structure_region']);
        $menu->addChild('Trash',          ['route' => 'cms_admin.structure_trash']);

        return $menu;
    }

    public function tree(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('full_structure');
        $menu->setChildrenAttributes([
            'class' => 'filetree',
            'id'    => 'browser',
        ]);
        $menu->setExtra('translation_domain', false);

        dump($options);

//        $this->addChild($menu);

        return $menu;
    }

    protected function addChild(ItemInterface $menu, Folder $parent_folder = null): void
    {
        $rootFolder = $this->container->get('cms.context')->getSite()->getRootFolder();

        // Фикс с кешированием.
        $rootFolder = $this->em->find('CMSBundle:Folder', $rootFolder->getId());

        if (empty($rootFolder)) {
            $rootFolder = [];
        } else {
            $rootFolder = [$rootFolder];
        }

        $folders = (null === $parent_folder)
            //? $this->container->get('cms.folder')->findByParent(null)
            ? $rootFolder
            : $parent_folder->getChildren();

        if (empty($folders)) {
            return;
        }

        /** @var $folder Folder */
        foreach ($folders as $folder) {
            if ($folder->isDeleted()) {
                continue;
            }

            $uri = $this->container->get('router')->generate('cms_admin.structure_folder', ['id' => $folder->getId()]);

            $tpl = $folder->getTemplateSelf();
            if (!empty($tpl)) {
                $tpl = ', tpl_self: '.$tpl;
            }

            if (!empty($folder->getTemplateInheritable())) {
                $tpl .= ', tpl_inherit: '.$folder->getTemplateInheritable();
            }

            $label = $folder->getTitle().' <span style="color: #a8a8a8;">('.$this->container->get('cms.folder')->getUri($folder, false).$tpl.')</span>';

            if (!$folder->isActive()) {
                $label = '<span style="text-decoration: line-through;">'.$label.'</span>';
            }

            $position = $this->container->get('translator')->trans('Position');

            $menu->addChild($folder->getTitle(), ['uri' => $uri])
                ->setAttributes([
                    'class' => 'folder',
                    'title' => $folder->getDescription().' ('.$position.' '.$folder->getPosition().')',
                    'id'    => 'folder_id_'.$folder->getId(),
                ])
                ->setLabel($label)
                ->setExtra('translation_domain', false)
            ;

            /** @var $sub_menu ItemInterface */
            $sub_menu = $menu[$folder->getTitle()];

            $this->addChild($sub_menu, $folder);

            if (empty($folder->getNodes())) {
                continue;
            }

            /** @var $node \Monolith\CMSBundle\Entity\Node */
            foreach ($folder->getNodes() as $node) {
                if ($node->isDeleted()) {
                    continue;
                }

                $moduleName =  substr($node->getModule(), 0, -12);

                $label = $moduleName;

                if (!empty($node->getDescription())) {
                    $label .= ': '.$node->getDescription();
                }

                if ($node->getRegionName() !== 'content') {
                    $label .= ' <span style="color: #a8a8a8;">(область: '.$node->getRegionName().')</span>';
                }

                if ($node->isNotActive()) {
                    $label = '<span style="text-decoration: line-through;">'.$label.'</span>';
                }

                $bundle = $this->container->get('kernel')->getBundle($node->getModule());
                if ($bundle instanceof ModuleBundle and !$bundle->isEnabled()) {
                    $label = '<span style="background-color: #c14b40; color: white;">'.$label.'</span>';
                }

                $uri = $this->container->get('router')->generate('cms_admin.structure_node_properties', ['id' => $node->getId()]);
                $sub_menu
                    ->addChild($node->getId(), ['uri' => $uri])
                    ->setAttributes([
                        'title' => 'node: '.$node->getId().', position: '.$node->getPosition(),
                        'id'    => 'node_id_'.$node->getId(),
                    ])
                    ->setLabel($label)
                    ->setExtra('translation_domain', false)
                ;
            }
        }
    }
}
