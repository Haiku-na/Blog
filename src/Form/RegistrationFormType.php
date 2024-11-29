<?php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter an email address.',
                    ]),
                ]
            ])
            ->add('roles', TextType::class, [
                'mapped' => false, // We handle roles separately in the controller
                'data' => 'ROLE_USER', // Default role
                'attr' => ['readonly' => true]
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('firstName', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your first name.',
                    ])
                ]
            ])
            ->add('lastName', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your last name.',
                    ])
                ]
            ])
            ->add('profilePicture', FileType::class, [
                'label' => 'Photo de profil',
                'required' => false,  // Le champ peut être vide
                'data_class' => null, // Pas d'entité associée, c'est juste un fichier
            ])

            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            // You can set 'created_at' and 'updated_at' in the controller, they are typically not set by the user
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
