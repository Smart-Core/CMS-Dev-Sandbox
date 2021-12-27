<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use SmartCore\CMSBundle\EntityCms\Domain;
use SmartCore\CMSBundle\EntityCms\Language;
use SmartCore\CMSBundle\EntityCms\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteMlModeOffFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Site $site */
        $site = $options['data'];

        $builder
            ->add('domain', EntityType::class, [
                'required' => false,
                'class'         => Domain::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')->where('e.parent IS NULL');
                },
            ])
        ;

        if ($site->getMultilanguageMode() === 'off') {
            $builder
                ->add('default_language', EntityType::class, [
                    'required'      => false,
                    'class'         => Language::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('e')->where('e.is_enabled = true')->orderBy('e.position', 'ASC');
                    },
                ])
            ;
        }

        $builder
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
        return 'smart_core_cms_site_ml_mode_off';
    }
}
