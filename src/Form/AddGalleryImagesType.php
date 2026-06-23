<?php

namespace App\Form;

use App\Entity\Gallery;
use App\Entity\GalleryImages;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Image;

class AddGalleryImagesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('files', FileType::class, [
            'mapped' => false,
            'multiple' => true,
            'constraints' => [
                new All([
                    new Image(
                        maxSize: '2M',
                        mimeTypes: ['image/jpeg','image/png','image/webp'],
                        mimeTypesMessage: 'L`image doit etre en JPEG,PNG ou WEBP et etre moins de 2MB'
                    ),
                ]),
            ],
            'attr' => [
                'accept' => 'image/jpeg, image/png, image/webp',
                'class' => 'inpt-img',
                'data-add-images-target' => 'fileInput'
            ],
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GalleryImages::class,
        ]);
    }
}
