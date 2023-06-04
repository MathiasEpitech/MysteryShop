<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom',TextType::class,[
                'required' => false,
                'label' => 'Nom'
            ])
            ->add('prenom',TextType::class,[
                'required' => false,
                'label' => 'Prenom'
            ])
            ->add('email',EmailType::class,[
                'required' => false,
                'label' => 'Email'
            ])
            ->add('roles',ChoiceType::class,[
                'choices' => [
                    'Admin' => 'ROLE_ADMIN',
                    'User' => 'ROLE_USER'
                ]
            ])
            ->add('adresse',TextType::class,[
                'required' => false,
                'label' => 'Adresse'
            ])
            ->add('codePostal',TextType::class,[
                'required' => false,
                'label' => 'Code postal'
            ])
            ->add('ville',TextType::class,[
                'required' => false,
                'label' => 'Ville'
            ])
            ->add('pays',TextType::class,[
                'required' => false,
                'label' => 'Pays'
            ])
        ;
        $builder->get('roles')
                ->addModelTransformer(new CallbackTransformer(
                    function ($rolesArray)
                    {
                        return count($rolesArray)? $rolesArray[0]:null;
                    }, 
                    function ($rolesString)
                    {
                        return [$rolesString];
                    }
                ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
