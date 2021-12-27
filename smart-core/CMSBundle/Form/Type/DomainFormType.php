<?php

declare(strict_types=1);

namespace SmartCore\CMSBundle\Form\Type;

use SmartCore\CMSBundle\EntityCms\Domain;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DomainFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
//            ->add('is_enabled')
            ->add('name', null, ['attr' => ['autofocus' => 'autofocus']])
            ->add('paid_till_date')
            //->add('position')
            ->add('comment')
        ;

        if ($options['data']->getParent()) {
            $builder->add('is_redirect', null, [
                'help' => 'Редиректить на базовый домен',
            ]);
        }

        $builder
            ->add('create', SubmitType::class, ['attr' => ['class' => 'btn-success']])
            ->add('update', SubmitType::class, ['attr' => ['class' => 'btn-success']])
            ->add('delete', SubmitType::class, ['attr' => ['class' => 'btn-danger', 'formnovalidate' => 'formnovalidate', 'onclick' => "return confirm('Вы уверены, что хотите удалить домен?')"]])
            ->add('cancel', SubmitType::class, ['attr' => ['class' => 'btn-light', 'formnovalidate' => 'formnovalidate']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Domain::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'smart_core_cms_domain';
    }
}
