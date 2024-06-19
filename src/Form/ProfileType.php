<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('oldPassword', PasswordType::class, [
                'mapped' => false,  // This field does not correspond to any entity attribute
                'required' => false,
                'constraints' => new NotBlank(['message' => 'Please enter your current password']),
            ])
            ->add('newPassword', PasswordType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Length(['min' => 6, 'max' => 4096]),
                ],
            ])
            ->add('confirmPassword', PasswordType::class, [
                'mapped' => false,
                'required' => false,
            ])
            ->add('profilePicture', FileType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Please upload a valid image file (JPEG, PNG, GIF)',
                    ]),
                ],
            ]);
    }
}
