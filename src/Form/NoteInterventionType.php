<?php
// src/Form/NoteInterventionType.php
namespace App\Form;

use App\Entity\NoteIntervention;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NoteInterventionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('contenu', TextareaType::class, [
                'label' => 'Note d\'intervention',
                'required' => true,
            ])
            ->add('photo', FileType::class, [
                'label' => 'Photo (facultatif)',
                'required' => false,
                'mapped' => false, // upload manuel
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NoteIntervention::class,
        ]);
    }
}
