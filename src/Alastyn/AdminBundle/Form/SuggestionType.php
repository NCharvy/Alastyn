<?php

namespace Alastyn\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SuggestionType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rss')
            ->add('site')
            ->add('domaine_existe')
            ->add('nomDomaine')
            ->add('adresse')
            ->add('codepostal')
            ->add('ville')
            ->add('nom')
            ->add('prenom')
            ->add('courriel')
            ->add('region')
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Alastyn\AdminBundle\Entity\Suggestion'
        ));
    }
}
