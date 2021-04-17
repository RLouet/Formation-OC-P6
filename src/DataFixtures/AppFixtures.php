<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Image;
use App\Entity\Message;
use App\Entity\Trick;
use App\Entity\User;
use App\Entity\Video;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('FR-fr');

        // Create an administrator
        $user = new User();
        $user
            ->setUsername('Admin')
            ->setEmail('contact@snowtricks.com')
            ->setEnabled(true)
            ->setSubscriptionDate($faker->dateTimeBetween($startDate = '-40 days', $endDate = '-31 days'))
            ->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            'admin'
        ));

        $manager->persist($user);

        // Create categories
        $categories = [
            'Grabs',
            'Rotations',
            'Flips',
            'Rotations désaxées',
            'Slides',
            'One foot',
            'Old school'
        ];

        for ($i = 0; $i < count($categories); $i++) {
            $category = new Category();
            $category->setName($categories[$i]);
            $categories[$i] = $category;
            $manager->persist($category);
        }

        //create tricks
        $tricks = [
            "1080" => [
                "categories" => [1],
                "images" => ["sample01.jpg","sample02.jpg","sample03.jpg"],
                "videos" => []
            ],
            "50-50" => [
                "categories" => [4,6],
                "images" => ["sample04.jpg","sample05.jpg"],
                "videos" => ["e-7NgSu9SXg"]
            ],
            "Backside Air" => [
                "categories" => [1],
                "images" => ["sample06.jpg","sample07.jpg","sample08.jpg"],
                "videos" => []
            ],
            "360 Nose Grab" => [
                "categories" => [0,1],
                "images" => ["sample09.jpg"],
                "videos" => ["gZFWW4Vus-Q","DmGcJsSGegc"]
            ],
            "Tail Grab" => [
                "categories" => [0],
                "images" => ["sample10.jpg","sample11.jpg"],
                "videos" => ["id8VKl9RVQw"]
            ],
            "720" => [
                "categories" => [1],
                "images" => ["sample12.jpg","sample13.jpg","sample14.jpg"],
                "videos" => ["H2MKP1epC7k"]
            ],
            "Mute Japan" => [
                "categories" => [0],
                "images" => ["sample15.jpg"],
                "videos" => ["CzDjM7h_Fwo"]
            ],
            "720 Stalefish" => [
                "categories" => [0,1],
                "images" => ["sample16.jpg","sample17.jpg"],
                "videos" => []
            ],
            "Misty Flip 540" => [
                "categories" => [1,2,3],
                "images" => ["sample18.jpg","sample19.jpg","sample20.jpg"],
                "videos" => ["hPuVJkw1MmI"]
            ],
            "Front Flip" => [
                "categories" => [2],
                "images" => ["sample21.jpg","sample22.jpg"],
                "videos" => ["gMfmjr-kuOg"]
            ],
            "Back flip" => [
                "categories" => [2],
                "images" => ["sample23.jpg"],
                "videos" => ["arzLq-47QFA","SlhGVnFPTDE"]
            ],
            "Cork" => [
                "categories" => [3],
                "images" => ["sample24.jpg","sample25.jpg"],
                "videos" => ["FMHiSF0rHF8"]
            ],
            "One foot Indy" => [
                "categories" => [0,5],
                "images" => ["sample26.jpg","sample27.jpg","sample28.jpg"],
                "videos" => ["LWUfrwCofuA"]
            ],
            "180 One foot Indy" => [
                "categories" => [0,1,5],
                "images" => ["sample29.jpg"],
                "videos" => []
            ],
            "Nose Grab" => [
                "categories" => [0],
                "images" => [],
                "videos" => []
            ],
            "Board Slide" => [
                "categories" => [4,6],
                "images" => ["sample30.jpg"],
                "videos" => []
            ],
            "180" => [
                "categories" => [1],
                "images" => [],
                "videos" => []
            ]
        ];

        foreach ($tricks as $trickName => $trickParams) {
            $newTrick = new Trick();
            $newTrick
                ->setAuthor($user)
                ->setName($trickName)
                ->setCreationDate($faker->dateTimeBetween($startDate = '-60 days', $endDate = '-15 days'))
                ->setDescription($faker->paragraphs($nb = mt_rand(2, 5), $asText = true))
            ;

            foreach ($trickParams['categories'] as $trickCategory) {
                $newTrick->addCategory($categories[$trickCategory]);
            }

            foreach ($trickParams['images'] as $trickImage) {
                $image = new Image();
                $image->setName($trickImage);
                $newTrick->addImage($image);
            }

            foreach ($trickParams['videos'] as $trickVideo) {
                $video = new Video();
                $video->setName($trickVideo);
                $newTrick->addVideo($video);
            }

            if (mt_rand(0,1)) {
                $newTrick->setEditDate($faker->dateTimeBetween($startDate = '-14 days', $endDate = 'now'));
            }

            if (mt_rand(0,1)) {
                $images = $newTrick->getImages();
                $nbImages = $images->count();
                if ($nbImages > 0) {
                    $newTrick->setHero($images->get(mt_rand(0, $nbImages - 1)));
                }
            }

            for ($i = 0; $i <= mt_rand(0, 20); $i++) {
                $message = new Message();
                $message
                    ->setAuthor($user)
                    ->setDate($faker->dateTimeBetween($startDate = '-14 days', $endDate = 'now'))
                    ->setContent($faker->paragraphs($nb = mt_rand(1, 3), $asText = true))
                ;
                $newTrick->addMessage($message);
            }
            $manager->persist($newTrick);
        }

        $manager->flush();
    }
}
