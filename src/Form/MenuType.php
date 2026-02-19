<?php

namespace App\Form;

use App\Entity\Menu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Dish;
use App\Repository\DishRepository;


class MenuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['label' => 'Titre'])
            ->add('themeLabel', TextType::class, ['label' => 'Thème', 'required' => false])

            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 3],
            ])
            ->add('conditions', TextareaType::class, [
                'label' => 'Conditions',
                'required' => false,
                'attr' => ['rows' => 4],
            ])

            ->add('minPeople', IntegerType::class, ['label' => 'Min. personnes'])
            // ✅ ton DECIMAL a scale=0 => prix entier (sans centimes)
            ->add('minPrice', NumberType::class, [
                'label' => 'Prix minimum (€)',
                'scale' => 0,
                'html5' => true,
                'attr' => ['step' => 1, 'min' => 0],
            ])
            ->add('entreeDish', EntityType::class, [
    'class' => Dish::class,
    'label' => 'Entrée (optionnel)',
    'mapped' => false,
    'required' => false,
    'placeholder' => 'Aucune',
    'choice_label' => 'name',
    'query_builder' => function (DishRepository $r) {
        return $r->createQueryBuilder('d')
            ->andWhere('d.type = :t')->setParameter('t', Dish::TYPE_ENTREE)
            ->orderBy('d.name', 'ASC');
    },
])
->add('platDish', EntityType::class, [
    'class' => Dish::class,
    'label' => 'Plat (optionnel)',
    'mapped' => false,
    'required' => false,
    'placeholder' => 'Aucun',
    'choice_label' => 'name',
    'query_builder' => function (DishRepository $r) {
        return $r->createQueryBuilder('d')
            ->andWhere('d.type = :t')->setParameter('t', Dish::TYPE_PLAT)
            ->orderBy('d.name', 'ASC');
    },
])
->add('dessertDish', EntityType::class, [
    'class' => Dish::class,
    'label' => 'Dessert (optionnel)',
    'mapped' => false,
    'required' => false,
    'placeholder' => 'Aucun',
    'choice_label' => 'name',
    'query_builder' => function (DishRepository $r) {
        return $r->createQueryBuilder('d')
            ->andWhere('d.type = :t')->setParameter('t', Dish::TYPE_DESSERT)
            ->orderBy('d.name', 'ASC');
    },
])


            ->add('stock', IntegerType::class, ['label' => 'Stock', 'required' => false])
            ->add('isActive', CheckboxType::class, ['label' => 'Actif', 'required' => false]);

            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Menu::class]);
    }
}
