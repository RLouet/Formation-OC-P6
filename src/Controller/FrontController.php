<?php


namespace App\Controller;


use App\Entity\Trick;
use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class FrontController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param TrickRepository $trickRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function home(TrickRepository $trickRepository, AuthenticationUtils $authenticationUtils) {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $tricks = $trickRepository->findAll();
        return $this->render('front/home.html.twig', [
            "tricks" => $tricks,
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * @Route("/tricks/{id}", name="home-tricks-single")
     * @param Trick $trick
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function tricksSingle(Trick $trick, AuthenticationUtils $authenticationUtils) {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('front/trick-view.html.twig', [
            "trick" => $trick,
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }
}