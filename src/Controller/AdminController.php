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
            $this->getParameter('app.users_pagination_length')
        );
        $paginateUsers = count($users) < $userRepository->count([]);
        return $this->render('admin/users-manage.html.twig', [
            'paginate_users' => $paginateUsers,
            'users' => $users,
        ]);
    }
}