<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Site\Form\Type;

use SmartCore\CMSBundle\Site\Entity\Folder;
use SmartCore\CMSBundle\Site\Form\Tree\FolderTreeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FolderFormType extends AbstractType
{
    public function __construct()
    {

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, ['attr' => ['autofocus' => 'autofocus']])
            ->add('slug')
            ->add('parent_folder', FolderTreeType::class)
            //->add('description')
            /*
            ->add('router_node_id', ChoiceType::class, [
                'choices'  => $routedNodes,
                'required' => false,
            ])
            ->add('position')
            ->add('is_active', null, ['required' => false])
            ->add('is_file',   null, ['required' => false])
            ->add('template_inheritable', ChoiceType::class, [
                'choices'  => $templates,
                'required' => false,
            ])
            ->add('template_self', ChoiceType::class, [
                'choices'  => $templates,
                'required' => false,
            ])
            //->add('meta', MetaFormType::class, ['label' => 'Meta tags'])
            //->add('permissions', 'text')
            //->add('lockout_nodes', 'text')
            //->addEventSubscriber(new FolderSubscriber())
            */
            ->add('create', SubmitType::class, ['attr' => ['class' => 'btn-success']])
            ->add('update', SubmitType::class, ['attr' => ['class' => 'btn-success']])
            ->add('cancel', SubmitType::class, ['attr' => ['class' => 'btn-light', 'formnovalidate' => 'formnovalidate']])
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Folder::class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'smart_core_cms_folder';
    }
}
