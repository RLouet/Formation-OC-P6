<?php


namespace App\Form;


use App\Entity\Image;
use App\Entity\Video;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Regex;

class ImageType extends AbstractType
{
    /** {@inheritdoc} */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('newFile', FileType::class, [
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
            /*->add('name', HiddenType::class, [
                'label' => false,
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[\w.]{6,255}$/i',
                        'message' => "Une erreur s'est produite."
                    ])
                ]
            ])*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
        ]);
    }

}