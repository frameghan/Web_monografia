<?php

namespace App\Form;

use App\Entity\Post;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType; 
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('image_name', FileType::class,[
                'label'=>'Imagen'
            ])
            ->add('post_content',TextareaType::class,[
                'attr'=>[
                    'rows'=>4
                ],
                'label'=>'Contenido'
            ]) 
          //  ->add('user')

        

            ->add('category', EntityType::class,[
                'class' => Category::class,
                'choice_label' => 'name',
                'label'=>'Categoria'


            ])
            ->add('save', SubmitType::class,[
                'label'=>'Registrar',
                'attr' => ['class' => 'btn btn-primary'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
