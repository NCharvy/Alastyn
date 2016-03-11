<?php

namespace Alastyn\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class DomaineType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom')
            ->add('adresse')
            ->add('codepostal')
            ->add('website')
            ->add('ville')
            ->add('region', EntityType::class, array(
                'class'         =>  'AlastynAdminBundle:Region',
                'choice_label'  =>  'nom',
                'multiple'      =>  false,
                'expanded'      =>  false
            ))
            ->add('enregistrer', SubmitType::class, array(
                'attr' => array('class' => 'btn btn-primary'),
            ))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Alastyn\AdminBundle\Entity\Domaine'
        ));
    }
}
