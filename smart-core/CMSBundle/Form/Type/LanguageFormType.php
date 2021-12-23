<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Form\Type;

use SmartCore\CMSBundle\EntityCms\Language;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LanguageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('is_enabled')
            ->add('name', null, [
                'attr' => [
                    'autofocus' => 'autofocus',
                    'placeholder' => 'English'
                ]
            ])
            ->add('code', null, [
                'attr' => [
                    'placeholder' => 'en_US'
                ]
            ])
            ->add('position')

            ->add('create', SubmitType::class, ['attr' => ['class' => 'btn-success']])
            ->add('update', SubmitType::class, ['attr' => ['class' => 'btn-success']])
            ->add('cancel', SubmitType::class, ['attr' => ['class' => 'btn-light', 'formnovalidate' => 'formnovalidate']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Language::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'smart_core_cms_language';
    }
}
