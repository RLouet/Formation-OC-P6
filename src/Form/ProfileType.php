<?php


namespace App\Form;


use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ProfileType extends AbstractType
{
    /** {@inheritdoc} */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
                $oldUsername = $event->getData()->getUsername();
                //dd($input);
                $event->getForm()->add('username', TextType::class, [
                    'label' => "Nom d'utilisateur",
                    'mapped' => false,
                    'attr' => [
                        'class' => 'form-control',
                        'value' => $oldUsername,
                    ],
                ]);
            })
            ->add('avatar', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'maxSizeMessage' => 'Ton image est trop lourde ({{ size }} {{ suffix }}). La taille maximum autorisée est {{ limit }} {{ suffix }}.',
                        'mimeTypes' => [
                            'image/png',
                            'image/gif',
                            'image/jpeg'
                        ],
                        'mimeTypesMessage' => 'Ton avatar doît être une image jpeg, png ou gif.'
                    ])
                ]
            ])
            ->add('email', EmailType::class, [
                'disabled' => true,
                'label' => "Email"
            ])
            ->add('plainPassword', RepeatedType::class, [
                'mapped' => false,
                'type' => PasswordType::class,
                'invalid_message' => "Les mots de passe doivent être identiques.",
                'required' => false,
                'first_options' => ['label' => 'Nouveau mot de passe'],
                'second_options' => ['label' => 'Confirme ton nouveau mot de passe'],
                'constraints' => [
                    new Regex('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,50}$/', 'Ton mot de passe doit contenir entre 8 et 50 caractères, au moins une lettre en minuscule, une lettre en majuscule et un chiffre.')
                ],
            ])
            ->add('originPassword', PasswordType::class, [
                'mapped' => false,
                'required' => true,
                'label' => 'Mot de passe actuel'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }

}