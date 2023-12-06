<?php

namespace App\Form;

use App\Entity\Cart;
use App\Entity\ContentCart;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentCartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantity')
            ->add('createdAt')
            ->add('cart', EntityType::class, [
                'class' => Cart::class,
'choice_label' => 'id',
            ])
            ->add('product', EntityType::class, [
                'class' => Product::class,
'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContentCart::class,
        ]);
    }
}
