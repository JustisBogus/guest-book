<?php

namespace App\Form;

use App\Entity\Message;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageType extends AbstractType
{
    public const HONEYPOT_FIELD_NAME = 'honeypot';
    public const TEXT_FIELD_NAME = 'text';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
            $builder->add(self::TEXT_FIELD_NAME, TextareaType::class, ['label' => false])
                ->add(self::HONEYPOT_FIELD_NAME, TextType::class, ['required' => false, 'label' => false])
                ->add('save', SubmitType::class);
                
    }

    public function configureOptions(OptionsResolver $resolver)
    {
            $resolver->setDefaults([
                'data_class' => Message::class
            ]);
    }
}