<?php


namespace App\Controller;


use App\Repository\MessageRepository;
use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
}