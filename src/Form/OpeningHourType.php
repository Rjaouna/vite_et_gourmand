<?php

namespace App\Form;

use App\Entity\OpeningHour;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OpeningHourType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $days = $options['days'] ?? [];

        $builder
            ->add('dayOfWeek', ChoiceType::class, [
                'label' => 'Jour',
                'choices' => array_flip($days),
            ])
            ->add('isClosed', CheckboxType::class, [
                'label' => 'Fermé',
                'required' => false,
            ])
            ->add('openTime', TimeType::class, [
                'label' => 'Ouverture',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('closeTime', TimeType::class, [
                'label' => 'Fermeture',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OpeningHour::class,
            'days' => [],
        ]);
    }
}
