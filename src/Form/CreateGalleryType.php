<?php

namespace App\Form;

use App\Entity\Gallery;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateGalleryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $gallery = $options['data'] ?? null; 
        $isCreateForm = $gallery instanceof Gallery ? ($gallery->getId() === null) : true;

        $builder
            ->add('name')
            ->add('image',FileType::class,[
                'constraints' => [new Image(
                    maxSize:'10M',
                    mimeTypes:['image/jpeg','image/png','image/webp'],
                    mimeTypesMessage:'L`image doit etre en JPEG,PNG ou WEBP et etre moins de 10MB',
                )],
                'mapped' => false,
                'required' => $isCreateForm ? true : false,
                'attr' => [
                    'data-newgallery-target' => 'fileInput'
                ]
            ])
        ;

        if (!$isCreateForm) {
            $builder->add("lastImage",HiddenType::class,[
                'required' => false,
                'mapped' =>false,
                'data' => $gallery->getSrc()
            ]);
        }
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Gallery::class,
        ]);
    }
}
