<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use SmartCore\CMSBundle\EntityCms\Domain;
use SmartCore\CMSBundle\EntityCms\Language;
use SmartCore\CMSBundle\EntityCms\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteFormType extends AbstractType
{
    public function __construct()
    {

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $themes = [];
//        foreach ($this->themeManager->all() as $item) {
//            $themes[$item['title'].' ('.$item['dirname'].')'] = $item['dirname'];
//        }

        $builder
            ->add('is_enabled')
            ->add('name', null, [
                'attr' => [
                    'autofocus' => 'autofocus',
                    'placeholder' => 'New site'
                ]
            ])
            ->add('multilanguage_mode', ChoiceType::class, [
                'choices'  => Site::getMultilanguageModeFormChoices(),
                'required' => true,
                'choice_translation_domain' => false,
            ])
            /*
            ->add('domain', EntityType::class, [
                'required' => false,
                'class'         => Domain::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')->where('e.parent IS NULL');
                },
            ])

            ->add('languages', null, [
                'expanded' => true,
                'multiple' => true,
            ])

            ->add('default_language', EntityType::class, [
                'required'      => false,
                'class'         => Language::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')->where('e.is_enabled = true')->orderBy('e.position', 'ASC');
                },
            ])
            */
            ->add('theme', ChoiceType::class, [
                'choices'  => $themes,
                'required' => false,
                'choice_translation_domain' => false,
            ])
            ->add('sub_path', null, [
                'attr' => [
                    'placeholder' => 'some-site1/',
                ],
                'help' => 'Подпуть сайта, например: domain.com/site1/',
                'translation_domain' => false,
            ])
            ->add('comment')

            ->add('create', SubmitType::class, ['attr' => ['class' => 'btn-success']])
            ->add('update', SubmitType::class, ['attr' => ['class' => 'btn-success']])
            ->add('cancel', SubmitType::class, ['attr' => ['class' => 'btn-light', 'formnovalidate' => 'formnovalidate']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Site::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'smart_core_cms_site';
    }
}
