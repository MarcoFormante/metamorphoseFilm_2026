<?php

namespace App\Form;

use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;

class NewProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $project = $options['data'] ?? null;
        
        $isCreateForm = $project instanceof Project ? ($project->getId() === null) : true;
        $images = [];
        if (!$isCreateForm) {
            $images = $project instanceof Project ? $project->getProjectImages() : null;
        }
        $builder
            ->add('isActive',CheckboxType::class,[
                'mapped' => false,
                'required' => false,
                'data' => $options['data'] ? $options['data']->isActive() : false,
                'label' => 'Actif'
            ])
            ->add('name',null,[
                'label' => 'Nom du projet'
            ])
            ->add('abrName',null,[
                'label' => 'Nom pour petits ecrans'
            ])
            ->add('slug',null,[
                'label' => 'URL-(SLUG)'
            ])
            ->add('made_by',null,[
                'label' => 'Realisé par'
            ])
            ->add('youtube_video',null,[
                'label' => 'YouTube Link'
            ])
            ->add('collab_with',null,[
                'required' =>true,
                'label' =>'Collab avec'
            ])
            ->add('background_video',FileType::class,[
                'mapped' => false,
                'required' => $isCreateForm,
                'constraints' => [
                    new File(
                        maxSize:'25M',
                        mimeTypes:['video/mp4'],
                        mimeTypesMessage:'La Video doit etre en MP4',
                        maxSizeMessage:'La video ne doit pas dépasser 10 MB.',
                        groups: ['Default', 'create']
                    ), new NotBlank(
                        message:"La video est obligatoire",
                        groups: ['create']
                    )
                ],
                   
                'attr' => [
                    'accept' => 'video/mp4',
                    'class' =>'inpt-bg-video line-bottom',
                    'name' => "backgroundVideo",
                    ( !$isCreateForm ? 'data-last' : "create") => !$isCreateForm ? $project->getBackgroundVideo() : "true"
                ]
            ])
        ;
        
        for ($i=1; $i <= 6 ; $i++) { 
            $lineBottomClass = $i === 6 ? 'line-bottom' : '' ;
            $builder->add("image" . $i ,FileType::class,[
                'mapped' => false,
                'required' => $isCreateForm,
                'constraints' => [
                    new Image(
                        maxSize:'10M',
                        mimeTypes:['image/jpeg','image/png','image/webp'],
                        mimeTypesMessage:"L`image-{$i} doit etre en JPEG,PNG ou WEBP et etre moins de 10MB",
                        maxSizeMessage: "L'image-{$i} ne doit pas dépasser 10 MB.",
                        groups: ['Default', 'create']
                    ),
                    new NotBlank(
                        message:"L'image-{$i}  est obligatoire",
                        groups: ['create']
                    )
                ],
                'attr' => [
                    'accept' => 'image/jpeg, image/png, image/webp',
                    'class' => "inpt-img {$lineBottomClass}",
                   ( !$isCreateForm ? 'data-last' : "create") => !$isCreateForm ? $images[$i - 1]?->getSrc() : "true"
                ]
            ]);
            if (!$isCreateForm) {
                $builder->add("lastImage" . $i ,HiddenType::class,[
                    'data' => $images[$i - 1] ? $images[$i - 1]->getSrc() : "",
                    'mapped' => false,
                    'attr' => ['hidden' => true,'class' => 'lastImageInput']
                ]);
            }
        }

        $builder->Add('projectStaff',ProjectStaffType::class,[
                'label' => "staff",
                'validation_groups' => ['Default', 'create'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
            'allow_extra_fields' => true,
        ]);
    }
}
