<?php

namespace SmartCore\Module\Menu\Form\Type;

use Smart\CoreBundle\Form\TypeResolverTtait;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemPropertiesFormType extends AbstractType
{
    use TypeResolverTtait;

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'properties'  => [],
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['properties'] as $name => $type) {
            $type = $this->resolveTypeName($type);

            $builder->add($name, $type, [
                'required' => false,
            ]);
        }
    }

    public function getBlockPrefix()
    {
        return 'smart_module_menu_item_properties';
    }
}
