<?php

namespace App\Form;

use App\Entity\Inscription;
use App\Entity\NotesEtudiant;
use App\Entity\Semestre;
use App\Entity\Ue;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotesEtudiantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('moyenne')
            ->add('inscription',EntityType::class,[
                'class'=>Inscription::class,
                'choice_label'=>'etudiant.nom'
            ])
            ->add('Ue',EntityType::class,[
                'class'=>Ue::class,
                'choice_label'=>'matiere.nom'
            ])
            ->add('semestre',EntityType::class,[
                'class'=>Semestre::class,
                'choice_label'=>'nom'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NotesEtudiant::class,
        ]);
    }
}
