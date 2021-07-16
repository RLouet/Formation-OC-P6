<?php


namespace App\Controller;


use App\Entity\User;
use App\Form\ProfileType;
use App\Repository\UserRepository;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route("/profile", name: "profile_edit")]
    public function profileEdit(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $manager, UploadService $uploadService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $usernameExists = $userRepository->findOneBy([
                'username' => $form['username']->getData()
            ]);
            if ($usernameExists && $usernameExists !== $user) {
                $form->get('username')->addError(new FormError("Le nom d'utilisateur demandé est déjà pris !"));
            }
            if (!$passwordHasher->isPasswordValid($user, $form['originPassword']->getData())) {
                $form->get('originPassword')->addError(new FormError("Ton mot de passe est incorrect !"));
            }
            if ($form->isValid()) {
                $user->setUsername($form['username']->getData());
                if (!empty($form['plainPassword']->getData())) {
                    $passwordEncoded = $passwordHasher->hashPassword($user, $form['plainPassword']->getData());
                    $user->setPassword($passwordEncoded);
                }

                $avatarFile = $uploadService->getFormFile($form, 'avatar');
                if ($avatarFile) {
                    $upload = $uploadService->uploadAvatar($avatarFile, $user);
                    //dd($upload);
                    if (!$upload['success']) {
                        $this->addFlash(
                            'danger',
                            "Une erreur s'est produite lors de l'enregistrement de ton avatar."
                        );
                        return $this->redirectToRoute("profile_edit");
                    }

                    $user->setAvatar($upload['file']);
                }
                $manager->persist($user);
                $manager->flush();
                $this->addFlash(
                    'primary',
                    "Ton profil a bien été modifié !"
                );
                return $this->redirectToRoute("front_home");
            }
        }

        return $this->render('profile/profile-edit.html.twig', [
            'form' => $form->createView(),
            'avatarUrl' => $user->getAvatarUrl()
        ]);
    }
}