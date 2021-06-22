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

        return $this->render('front/trick-add.html.twig', [
            "trick" => $trick,
            "form" => $form->createView()
        ]);
    }

    #[Route("/tricks/edit/{id}", name: "front_tricks-edit")]
    public function editTrick(Trick $trick, AuthenticationUtils $authenticationUtils): Response
    {
        $user = $this->getUser();

        if (!($this->isGranted('ROLE_ADMIN') || $trick->getAuthor() === $user)) {
            //return $this->redirectToRoute('front_tricks-single', ['id' => $trick->getId()]);
            throw $this->createAccessDeniedException();
        }
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