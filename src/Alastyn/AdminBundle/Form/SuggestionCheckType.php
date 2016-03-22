<?php

namespace Alastyn\AdminBundle\Form;

use Alastyn\AdminBundle\Repository\DomaineRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\ORM\QueryBuilder;

class SuggestionCheckType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rss')
            ->add('nomDomaine')
            ->add('domaine', EntityType::class, array(
                'class'         =>  'AlastynAdminBundle:Domaine',
                'choice_label'  =>  'nom',
                'multiple'      =>  false,
                'expanded'      =>  false,
                'placeholder'   =>  'Sélectionner',
                'empty_data'   =>  null,
                'query_builder' =>  function(DomaineRepository $repository) use ($options){
                        return $repository->createQueryBuilder('domaine')
                                        ->select('domaine')
                                        ->where('domaine.nom LIKE :nomD')
                                        ->setParameter('nomD', '%' . $options['nomdomaine'] . '%');
                        },
                'required' => false
            ))
            ->add('enregistrer', SubmitType::class, array(
                'attr' => array('class' => 'btn btn-primary')
            ))
        ;
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Alastyn\AdminBundle\Entity\Suggestion',
            'nomdomaine' => ''
        ));
    }
}
