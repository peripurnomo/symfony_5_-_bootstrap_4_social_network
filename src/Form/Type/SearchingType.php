<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('search', SearchType::class, [
        	'label' => false,
        	'attr' => [
        		'class' => 'form-control mr-sm-2',
        		'placeholder' => 'Search people'
        	]
        ]);
    }
}