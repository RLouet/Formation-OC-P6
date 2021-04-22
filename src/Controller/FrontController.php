<?php


namespace App\Controller;


use App\Entity\Trick;
use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param TrickRepository $trickRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function home(TrickRepository $trickRepository) {
        $tricks = $trickRepository->findAll();
        return $this->render('front/home.html.twig', [
            "tricks" => $tricks
        ]);
    }

    /**
     * @Route("/tricks/{id}", name="home-tricks-single")
     * @param Trick $trick
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function tricksSingle(Trick $trick) {
        return $this->render('front/trick-view.html.twig', [
            "trick" => $trick
        ]);
    }
}