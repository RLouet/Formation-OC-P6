<?php


namespace App\Controller;


use App\Repository\MessageRepository;
use App\Repository\TrickRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class AjaxController extends AbstractController
{
    /**
     * @Route("/ajax/loadtricks",
     *     name="ajax-load-tricks",
     *     methods={"POST"},
     *     condition="request.headers.get('X-Requested-With') matches '/XMLHttpRequest/i'")
     */
    public function loadTricks(Request $request, TrickRepository $trickRepository, SerializerInterface $serializer): JsonResponse
    {
        $response = [];
        $response['itemsData'] = $trickRepository->findBy(
            [],
            ['id' => 'ASC'],
            $this->getParameter('app.tricks_pagination_length'),
            $request->get('offset')
        );
        $response['end'] = count($response['itemsData']) + $request->get('offset') >= $trickRepository->count([]);

        $response = $serializer->serialize(
            $response,
            'json',
            ['groups' => 'paginate_trick']
        );
        return JsonResponse::fromJsonString($response);
    }

    /**
     * @Route("/ajax/loadmessages",
     *     name="ajax-load-messages",
     *     methods={"POST"},
     *     condition="request.headers.get('X-Requested-With') matches '/XMLHttpRequest/i'")
     */
    public function loadMessages(Request $request, MessageRepository $messageRepository, SerializerInterface $serializer): JsonResponse
    {
        $response = [];

        $response['itemsData'] = $messageRepository->findBy(
            ['trick' => $request->get('parentId')],
            ['date' => 'DESC'],
            $this->getParameter('app.comments_pagination_length'),
            $request->get('offset')
        );
        $response['end'] = count($response['itemsData']) + $request->get('offset') >= $messageRepository->count(['trick' => $request->get('parentId')]);

        $response = $serializer->serialize(
            $response,
            'json',
            ['groups' => 'paginate_message']
        );
        return JsonResponse::fromJsonString($response);
    }

    /**
     * @Route("/ajax/loadusers",
     *     name="ajax-load-users",
     *     methods={"POST"},
     *     condition="request.headers.get('X-Requested-With') matches '/XMLHttpRequest/i'")
     */
    public function loadUsers(Request $request, UserRepository $userRepository, SerializerInterface $serializer): JsonResponse
    {
        $response = [];

        $response['itemsData'] = $userRepository->findBy(
            [],
            ['id' => 'ASC'],
            $this->getParameter('app.users_pagination_length'),
            $request->get('offset')
        );
        $response['end'] = count($response['itemsData']) + $request->get('offset') >= $userRepository->count([]);

        $response = $serializer->serialize(
            $response,
            'json',
            ['groups' => 'paginate_user']
        );
        return JsonResponse::fromJsonString($response);
    }

    /**
     * @Route("admin/ajax/switchrole",
     *     name="ajax-switch-role",
     *     methods={"POST"},
     *     condition="request.headers.get('X-Requested-With') matches '/XMLHttpRequest/i'")
     */
    public function switchRole(Request $request, UserRepository $userRepository, UserInterface $currentUser, EntityManagerInterface $manager): JsonResponse
    {
        $response['success'] = false;

        $user = $userRepository->find($request->get('user_id'));

        if (!$this->isCsrfTokenValid('switch-user-role', $request->get('_csrf_token'))) {
            $response['error'] = "Une erreur s'est produite.";
            return new JsonResponse($response);
        }

        if (!$user) {
            $response['error'] = "L'utilisateur est invalide.";
            return new JsonResponse($response);
        }

        if ($user === $currentUser) {
            $response['error'] = "Tu ne peux pas modifier ton propre rôle.";
            return new JsonResponse($response);
        }

        $user->switchRole();
        $manager->flush();

        $response['success'] = true;
        $response['user'] = ['id' => $user->getId(), 'role' => $user->getRoles()];

        return new JsonResponse($response);
    }

    #[Route("/admin/ajax/deleteuser",
        name: "ajax-delete-user",
        methods: ["POST"],
        condition: "request.headers.get('X-Requested-With') matches '/XMLHttpRequest/i'"
    )]
    public function deleteUser(Request $request, UserRepository $userRepository, UserInterface $currentUser, EntityManagerInterface $manager): JsonResponse
    {
        $response['success'] = false;

        $user = $userRepository->find($request->get('user_id'));

        if (!$this->isCsrfTokenValid('delete-user', $request->get('_csrf_token'))) {
            $response['error'] = "Une erreur s'est produite.";
            return new JsonResponse($response);
        }

        if ($user === $currentUser) {
            $response['error'] = "Tu ne peux pas modifier ton propre rôle.";
            return new JsonResponse($response);
        }

        if ($user) {
            $response['user'] = ['id' => $user->getId()];
            $manager->remove($user);
            $manager->flush();
        }
        $response['success'] = true;

        return new JsonResponse($response);
    }
}