<?php


namespace App\Controller;


use App\Form\ProfileType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="profile_edit")
     */
    public function profileEdit(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $usernameExists = $userRepository->findOneBy([
                'username' => $form['username']->getData()
            ]);
            if ($usernameExists && $usernameExists != $user) {
                $form->get('username')->addError(new FormError("Le nom d'utilisateur demandé est déjà pris !"));
            }
            if (!$passwordEncoder->isPasswordValid($user, $form['originPassword']->getData())) {
                $form->get('originPassword')->addError(new FormError("Ton mot de passe est incorrect !"));
            }
            if ($form->isValid()) {
                $this->addFlash(
                    'notice',
                    "success."
                );
                $user->setUsername($form['username']->getData());
                if (!empty($form['plainPassword']->getData())) {
                    $passwordEncoded = $passwordEncoder->encodePassword($user, $form['plainPassword']->getData());
                    $user->setPassword($passwordEncoded);
                }
                $manager->persist($user);
                $manager->flush();
                return $this->redirectToRoute("front_home");
            }
        }

        return $this->render('profile/profile-edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}