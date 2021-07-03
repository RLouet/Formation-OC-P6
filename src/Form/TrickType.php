<?php


namespace App\Form;


use App\Entity\Category;
use App\Entity\Image;
use App\Entity\Trick;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrickType extends AbstractType
{
    /** {@inheritdoc} */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
                $input = $event->getData();
                $heroItemClass = null;
                $heroId = $input->getHero()?$input->getHero()->getId():null;
                if ($heroId) {
                    $heroItemClass = "old-";
                    $heroItemClass .= array_key_first($event->getForm()['images']->getData()->filter(function(Image $image) use ($heroId) {
                        return $image->getId() === $heroId;
                    })->toArray());
                }
                //dd($input);
                $event->getForm()->add('hero', HiddenType::class, [
                    'mapped' => false,
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control',
                        'value' => $heroItemClass,
                    ],
                ]);
            })
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'rows' => 10,
                ]
            ])
            ->add('categories', EntityType::class, [
                'class' => Category::class,
                'label_attr' => [
                    'class' => 'checkbox-inline'
                ],
                'choice_label' => 'name',
                'required' => true,
                'label' => 'Catégories',
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('videos', CollectionType::class, [
                'entry_type'=> VideoType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'by_reference' => false,
            ])
            ->add('newImages', CollectionType::class, [
                'mapped' => false,
                'entry_type'=> NewImageType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'by_reference' => false,
            ])
            ->add('images', CollectionType::class, [
                'entry_type'=> ImageType::class,
                'entry_options' => ['label' => false],
                'allow_delete' => true,
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }

}