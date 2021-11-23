<?php

namespace SmartCore\Module\Menu\Form\Type;

use SmartCore\Module\Menu\Entity\Menu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, ['attr' => ['autofocus' => 'autofocus']])
            ->add('description')
            ->add('position')
            ->add('properties')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Menu::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'smart_module_menu';
    }
}
