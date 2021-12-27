<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use SmartCore\CMSBundle\EntityCms\Domain;
use SmartCore\CMSBundle\EntityCms\Language;
use SmartCore\CMSBundle\EntityCms\Site;
use SmartCore\CMSBundle\EntityCms\SiteLanguage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteLanguageFormType extends AbstractType
{
    public function __construct()
    {

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Site $site */
        $site = $options['data']->getSite();

        $themes = []; // @todo

        $builder
            ->add('language', EntityType::class, [
                'class'         => Language::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')->where('e.is_enabled = true')->orderBy('e.position', 'ASC');
                },
            ])
        ;

        if ($site->getMultilanguageMode() === Site::MULTILANGUAGE_MODE_DOMAIN) {
            $builder
                ->add('domain', EntityType::class, [
                    'required'      => true,
                    'class'         => Domain::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('e')->where('e.parent IS NULL');
                    },
                ])
            ;
        }

        /*
        $builder
            ->add('theme', ChoiceType::class, [
                'choices'  => $themes,
                'required' => false,
                'choice_translation_domain' => false,
            ])
        ;
        */

        $builder
            ->add('create', SubmitType::class, ['attr' => ['class' => 'btn-success']])
            ->add('update', SubmitType::class, ['attr' => ['class' => 'btn-success']])
            ->add('delete', SubmitType::class, ['attr' => ['class' => 'btn-danger', 'formnovalidate' => 'formnovalidate', 'onclick' => "return confirm('Вы уверены, что хотите удалить язык?')"]])
//            ->add('cancel', SubmitType::class, ['attr' => ['class' => 'btn-light', 'formnovalidate' => 'formnovalidate']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SiteLanguage::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'smart_core_cms_site_language';
    }
}
