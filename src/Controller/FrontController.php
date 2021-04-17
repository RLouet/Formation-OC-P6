<?php


namespace App\Controller;


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
}