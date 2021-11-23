<?php

namespace SmartCore\Module\Menu\Form\Tree;

use Doctrine\Common\Persistence\ObjectManager;
use SmartCore\Module\Menu\Entity\MenuItem;
use Symfony\Bridge\Doctrine\Form\ChoiceList\DoctrineChoiceLoader;
use Symfony\Bridge\Doctrine\Form\Type\DoctrineType;
use Symfony\Component\Form\ChoiceList\Factory\CachingFactoryDecorator;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemTreeType extends DoctrineType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        /**
         * Здесь требуется внедрить опцию 'menu' в Loader.
         * Код скопирован из DoctrineType::configureOptions()
         *
         * @param Options $options
         *
         * @return DoctrineChoiceLoader
         */
        $choiceLoader = function (Options $options) {
            // Unless the choices are given explicitly, load them on demand
            if (null === $options['choices']) {
                $qbParts = null;

                if (null !== $options['query_builder']) {
                    $entityLoader = $this->getLoader($options['em'], $options['query_builder'], $options['class']);
                } else {
                    $queryBuilder = $options['em']->getRepository($options['class'])->createQueryBuilder('e');
                    $entityLoader = $this->getLoader($options['em'], $queryBuilder, $options['class']);
                }

                // !!! Вот здесь инжектится опция.
                $entityLoader->setMenu($options['menu']);

                $doctrineChoiceLoader = new DoctrineChoiceLoader(
                    $options['em'],
                    $options['class'],
                    $options['id_reader'],
                    $entityLoader
                );

                return $doctrineChoiceLoader;
            }
        };

        $resolver->setDefaults([
            'choice_label'  => 'form_title',
            'class'         => MenuItem::class,
            'choice_loader' => $choiceLoader,
            'menu'          => null,
            'required'      => false,
        ]);
    }

    public function getLoader(ObjectManager $manager, $queryBuilder, $class)
    {
        return new ItemLoader($manager, $queryBuilder, $class);
    }

    public function getBlockPrefix()
    {
        return 'smart_module_menu_item_tree';
    }
}
