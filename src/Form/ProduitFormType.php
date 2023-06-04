<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Produitmf;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ProduitFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class,[
                'required' => false,
                'attr' => [
                    'label' => 'Nom du produit']
                
            ])

            ->add('categorie', EntityType::class,[
                'required' => false,
                'placeholder' => '---Veuillez choisir une categorie---',
                'class' => Categorie::class,
                'choice_label' => function (Categorie $categorie)
                {
                    return $categorie->getNom();
                }
                
            ])

            ->add('description',TextareaType::class,[
                'required' => false,
                'attr' => [
                    'label' => 'Description du produit']
            ])
            ->add('prix',MoneyType::class,[
                'required' => false,
                'attr' => [
                    'label' => 'Prix du produit']
            ])
            ->add('image',FileType::class,[
                'required' => false,
                'label' => "Ajouter l'image du produit",
                'attr' => 
                [
                ],
                'mapped' => false,
                'constraints' =>
                [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/jpg',
                            'image/png',
                            'image/gif'
                        ],
                        'mimeTypesMessage'=> 'Extensions acceptés: jpeg/jpg/png/gif'
                    ])
                ]
            ])
            ->add('stock',NumberType::class,[
                'required' => false,
                'attr' => [
                    'label' => 'Stock disponible du produit']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produitmf::class,
        ]);
    }
}
