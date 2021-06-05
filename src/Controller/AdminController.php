<?php


namespace App\Controller;


use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin_users")
     */
    public function admin(UserRepository $userRepository): Response
    {

        $users = $userRepository->findBy(
            [],
            ['id' => 'ASC'],
            $this->getParameter('app.tricks_pagination_length')
        );
        return $this->render('admin/users-manage.html.twig', [
            'users' => $users,
        ]);
    }
}