<?php

namespace Alastyn\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom')
            ->add('pays', 'entity', array(
                'class'         =>  'AlastynAdminBundle:Pays',
                'property'      =>  'nom',
                'multiple'      =>  false,
                'expanded'    =>  false
            ))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Alastyn\AdminBundle\Entity\Region'
        ));
    }
}
