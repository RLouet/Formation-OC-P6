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

        $tricks = $trickRepository->findBy(
            [],
            ['id' => 'ASC'],
            $this->getParameter('app.tricks_pagination_length')
        );
        $paginateTricks = count($tricks) < $trickRepository->count([]);
        return $this->render('front/home.html.twig', [
            'paginate_tricks' => $paginateTricks,
            'tricks' => $tricks,
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

        $paginateComments =  $this->getParameter('app.comments_pagination_length') < $trick->getMessages()->count();
        return $this->render('front/trick-view.html.twig', [
            'paginate_comments' => $paginateComments,
            "trick" => $trick,
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }
}