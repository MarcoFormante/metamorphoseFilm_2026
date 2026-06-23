<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\ProjectStaff;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectStaffType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('production')
            ->add('artists')
            ->add('montage')
            ->add('cadrage')
            ->add('droniste')
            ->add('ph_plateau')
            ->add('decorateurs')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectStaff::class,
        ]);
    }
}
