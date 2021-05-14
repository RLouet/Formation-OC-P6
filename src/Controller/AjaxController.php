<?php


namespace App\Controller;


use App\Repository\TrickRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AjaxController extends AbstractController
{
    /**
     * @Route("/ajax/loadtricks",
     *     name="ajax-load-tricks",
     *     methods={"POST"},
     *     condition="request.headers.get('X-Requested-With') matches '/XMLHttpRequest/i'")
     */
    public function loadTricks(Request $request, TrickRepository $trickRepository): JsonResponse
    {
        $response['itemsHtml'] = [];

        $tricks = $trickRepository->findBy(
            [],
            ['id' => 'ASC'],
            $this->getParameter('app.pagination_length'),
            $request->get('offset')
        );

        foreach ($tricks as $trick) {
            $response['itemsHtml'][] = $this->renderView('front/trick-item.html.twig', [
                "trick" => $trick
            ]);
        }

        $response['end'] = count($response['itemsHtml']) + $request->get('offset') >= $trickRepository->count([]);

        return new JsonResponse($response);
    }
}