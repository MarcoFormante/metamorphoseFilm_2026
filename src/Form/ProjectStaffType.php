<?php

namespace App\Form;

use App\Entity\ProjectStaff;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectStaffType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('production')
            ->add('artists',null,['label' => 'Artistes'])
            ->add('montage')
            ->add('cadrage')
            ->add('droniste',null,['label' =>'Pilote de drone'])
            ->add('ph_plateau')
            ->add('decorateurs',null,[
                'label' => 'Chef Opérateur'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectStaff::class,
        ]);
    }
}
