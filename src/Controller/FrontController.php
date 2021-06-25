<?php


namespace App\Controller;


use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Video;
use App\Form\TrickType;
use App\Repository\TrickRepository;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class FrontController extends AbstractController
{
    #[Route("/", name: "front_home")]
    public function home(TrickRepository $trickRepository, AuthenticationUtils $authenticationUtils): Response
    {
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

    #[Route("/tricks/details/{id}", name: "front_tricks-single")]
    public function tricksSingle(Trick $trick, AuthenticationUtils $authenticationUtils): Response
    {
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

    #[Route("/tricks/add", name: "front_tricks-add")]
    public function addTrick(Request $request, EntityManagerInterface $manager, UploadService $uploadService): Response
    {
        $trick = new Trick();
        /*$video = new Video();
        $video->setName('testvideoname');
        $trick->addVideo($video);*/

        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            //dd ($form['newImages'][1]['name']);
            if ($form->isValid()) {
                $trick->setAuthor($this->getUser());
                $hero = false;
                preg_match_all("/^(?<type>new|old)-(?<index>\d{1,4})$/i",$form->get('hero')->getData(), $matches);
                if (!empty($matches[0])) {
                    $hero = [
                        'all' => (string)$matches[0][0],
                        'type' => (string)$matches['type'][0],
                        'index' => (int)$matches['index'][0]
                    ];
                }
                //dd($hero);

                $uploadError = false;
                foreach ($form['newImages'] as $key => $imageForm) {
                    $imageFile = $uploadService->getFormFile($form->get('newImages')[$key], 'name');
                    //dd($image, $imageFile);
                    if ($imageFile) {
                        $upload = $uploadService->uploadTrickImage($imageFile, $trick);
                        //dd($upload);
                        if (!$upload['success']) {
                            $uploadError = true;
                            $this->addFlash(
                                'danger',
                                "Une erreur s'est produite lors de l'enregistrement d'une image'."
                            );
                        }
                        if ($upload['success']) {
                            $image = new Image();
                            $image->setName($upload['file']);
                            $trick->addImage($image);
                            if ($hero && $hero['type'] === "new" && $hero['index'] === $key) {
                                $trick->setHero($image);
                            }
                        }
                    }
                }
                //dd('ok');
                $manager->persist($trick);
                $manager->flush();

                $this->addFlash(
                    'primary',
                    "Ton trick a bien été enregistré."
                );

                if ($uploadError) {
                    return $this->redirectToRoute('front_tricks-edit', ['id' => $trick->getId()]);
                }
                return $this->redirectToRoute('front_home');
            }
        }

        return $this->render('front/trick-add-edit.html.twig', [
            "trick" => $trick,
            "form" => $form->createView()
        ]);
    }

    #[Route("/tricks/edit/{id}", name: "front_tricks-edit")]
    public function editTrick(Trick $trick, Request $request): Response
    {
        $user = $this->getUser();

        if (!($this->isGranted('ROLE_ADMIN') || $trick->getAuthor() === $user)) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        return $this->render('front/trick-add-edit.html.twig', [
            "trick" => $trick,
            "form" => $form->createView()
        ]);
    }

    #[Route("/tricks/delete", name: "front_trick-delete")]
    public function deleteTrick(Request $request, TrickRepository $trickRepository, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        $trick = $trickRepository->find($request->get('trick_id'));


        if (!$trick || (!$this->isGranted('ROLE_ADMIN') && $user !== $trick->getAuthor())) {
            $this->addFlash(
                'danger',
                "Le trick est invalide."
            );
            return $this->redirectToRoute('front_home');
        }

        if (!$this->isCsrfTokenValid('delete-trick', $request->get('_csrf_token'))) {
            $this->addFlash(
                'danger',
                "Une erreur s'est produite."
            );
            return $this->redirectToRoute('front_tricks-single', ['id' => $trick->getId()]);
        }

        $manager->remove($trick);
        $manager->flush();

        $this->addFlash(
            'primary',
            "Le trick a bien été supprimé."
        );
        return $this->redirect($this->generateUrl("front_home") . "#tricksList");
    }
}