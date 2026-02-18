<?php

namespace App\Form;

use App\Entity\Menu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Titre'])
            ->add('themeLabel', TextType::class, ['label' => 'Thème', 'required' => false])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false])
            ->add('conditions', TextareaType::class, ['label' => 'Conditions', 'required' => false])
            ->add('minPeople', IntegerType::class, ['label' => 'Nombre minimum de personnes'])
            ->add('minPrice', MoneyType::class, [
                'label' => 'Prix minimum',
                'currency' => 'EUR',
                'required' => true,
            ])
            ->add('stock', IntegerType::class, ['label' => 'Stock', 'required' => false])
            ->add('isActive', CheckboxType::class, ['label' => 'Actif', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Menu::class,
        ]);
    }
}
