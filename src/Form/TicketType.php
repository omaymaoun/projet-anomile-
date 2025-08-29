<?php

namespace App\Form;

use App\Entity\Ticket;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('description')
            ->add('localisation', null, [
                'label' => 'Localisation (ex : Bâtiment A, Salle 102)',
                'attr' => [
                    'placeholder' => 'Saisir la localisation du problème...'
                ],
            ])
            ->add('categorie', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Hard (Matériel)' => 'Hard',
                    'Soft (Logiciel/Configuration)' => 'Soft',
                ],
                'placeholder' => 'Choisir une catégorie',
            ])

             ->add('criticite', ChoiceType::class, [
            'label' => 'Criticité',
            'choices' => [
                'Critique (arrêt total / impact majeur)' => 'Critique',
                'Majeure (impact important mais contournement possible)' => 'Majeure',
                'Mineure (gêne limitée / impact faible)' => 'Mineure',
            ],
            'placeholder' => 'Choisir la criticité',
        ])
            ->add('photo', FileType::class, [
                'label' => 'Photo (JPG/PNG)',
                'required' => false,
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ticket::class,
        ]);
    }
}
