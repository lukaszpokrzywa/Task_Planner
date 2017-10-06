<?php

namespace TaskPlannerBundle\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityRepository;

class TaskType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name')
            ->add('description')
            ->add('deadline')
            ->add('priority')
            ->add('isDone')
            ->add('category', EntityType::class, array(
                'class' => 'TaskPlannerBundle:Category',
                'choice_label' => 'name',
                /*
                'query_builder' => function(EntityRepository $repo) use($options) {
                    return $repo->getCategoriesByUser($options['user']);
                }*/
                'choices' => $options['user']->getCategories()
                ))
            ->add('save', SubmitType::class);
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'TaskPlannerBundle\Entity\Task',
            'user' => 'TaskPlannerBundle\Entity\User'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'taskplannerbundle_task';
    }


}
