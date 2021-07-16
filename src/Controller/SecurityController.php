<?php

namespace App\Controller;

use App\Entity\Token;
use App\Entity\User;
use App\Form\PasswordRecoveryType;
use App\Form\RegistrationType;
use App\Repository\TokenRepository;
use App\Repository\UserRepository;
use App\Service\Security\TokenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private ?string $lastUsername;
    private ?string $lastAuthError;

    public function __construct(AuthenticationUtils $authenticationUtils)
    {
        // get the login error if there is one
        $this->lastAuthError = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $this->lastUsername = $authenticationUtils->getLastUsername();
    }

    #[Route("/logout", name: "security_logout")]
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route("/registration", name: "security_registration")]
    public function registration(Request $request, UserRepository $userRepository, TokenRepository $tokenRepository, EntityManagerInterface $manager, UserPasswordHasherInterface $passwordHasher, TokenService $tokenService): Response
    {
        if ($this->getUser()) {
            $this->addFlash(
                'primary',
                "Tu es déjà connecté."
            );
            return $this->redirect($request->getSession()->get('origin_path')?:$this->generateUrl('front_home'));
        }
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            $expiredTokens = $tokenRepository->findExpired();
            foreach ($expiredTokens as $expiredToken) {
                if (!$expiredToken->getUser()->getEnabled()) {
                    $manager->remove($expiredToken->getUser());
                }
                $manager->remove($expiredToken);
            }
            $manager->flush();

            $mailExists = $userRepository->findOneBy([
                'email' => $user->getEmail()
            ]);

            $usernameExists = $userRepository->findOneBy([
                'username' => $user->getUsername()
            ]);

            $deleteUser = false;
            if ($mailExists || $usernameExists) {
                $deleteUser = true;
                if ($mailExists && $mailExists->getEnabled()) {
                    $this->addFlash(
                        'warning',
                        "Cette adresse Email est déjà active. Tu peux te connecter"
                    );
                    $request->getSession()->set(Security::LAST_USERNAME, $mailExists->getEmail());
                    return $this->redirect($request->getSession()->get('origin_path')?:$this->generateUrl('front_home') . '#login');
                }

                if ($usernameExists) {
                    $form->get('username')->addError(new FormError("Ce nom d'utilisateur est déjà pris !"));
                    $deleteUser = false;
                }
            }

            if ($form->isValid()) {

                if ($deleteUser) {
                    $userExists = $mailExists?:$usernameExists;

                    $oldToken = $tokenRepository->findOneBy([
                        'user' => $userExists
                    ]);
                    if ($oldToken) {
                        $manager->remove($oldToken);
                    }
                    $manager->remove($userExists);
                    $manager->flush();
                }

                $passwordEncoded = $passwordHasher->hashPassword($user, $form['plainPassword']->getData());
                $user->setPassword($passwordEncoded);
                $user->setRoles(['ROLE_USER']);
                $user->setSubscriptionDate(new \DateTime());

                $token = null;
                $tokenExists = true;
                while ($tokenExists !== null) {
                    $token = new Token($user);
                    $tokenExists = $tokenRepository->findOneBy([
                        'value' => $token->getValue()
                    ]);
                }

                if ($tokenService->sendRegistrationToken($token)) {
                    $manager->persist($token);
                    $manager->flush();

                    $this->addFlash(
                        'primary',
                        "Un Email de validation vient de t'être envoyé."
                    );
                    return $this->redirectToRoute('front_home');
                }

                $this->addFlash(
                    'danger',
                    "Une erreur s'est produite. Merci de rééssayer ultérieurement"
                );

                return $this->render('security/registration.html.twig', [
                    'form' => $form->createView(),
                    'last_username' => $this->lastUsername,
                    'error' => $this->lastAuthError
                ]);

            }

            $this->addFlash(
                'warning',
                "Les données du formulaire sont invalides."
            );
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView(),
            'last_username' => $this->lastUsername,
            'error' => $this->lastAuthError
        ]);
    }

    #[Route("/registration/confirmation/{value}", name: "security_registration_confirmation")]
    public function registrationConfirmation(Token $token, Request $request, EntityManagerInterface $manager): Response
    {
        $user = $token->getUser();

        if ($token->isValid() || $user->getEnabled()) {
            $user->setEnabled(true);
            $manager->remove($token);
            $manager->flush();

            $this->addFlash(
                'primary',
                "Ton inscription est confirmée ! Tu peux te connecter."
            );

            $request->getSession()->set(Security::LAST_USERNAME, $user->getEmail());
            return $this->redirect($this->generateUrl('front_home') . '#login');
        }

        $manager->remove($user);
        $manager->flush();
        $this->addFlash(
            'danger',
            "Désolé, ton lien a expiré ! Merci de t'inscrire à nouveau."
        );

        return $this->redirectToRoute('security_registration');
    }

    #[Route("/password/forgot", name: "security_password_forgot")]
    public function passwordForgot(Request $request, UserRepository $userRepository, TokenRepository $tokenRepository, EntityManagerInterface $manager, TokenService $tokenService): Response
    {
        if ($this->getUser()) {
            $this->addFlash(
                'primary',
                "Tu es déjà connecté."
            );
            return $this->redirect($request->getSession()->get('origin_path')?:$this->generateUrl('front_home'));
        }
        $submittedToken = $request->request->get('token');
        $submittedEmail = $request->request->get('email');

        if ($submittedEmail) {
            if ($this->isCsrfTokenValid('forgot-password', $submittedToken)) {
                $user = $userRepository->findOneBy(['email' => $submittedEmail]);
                if ($user) {
                    if ($user->getToken()) {
                        $manager->remove($user->getToken());
                        $manager->flush();
                    }
                    $token = null;
                    $tokenExists = true;
                    while ($tokenExists !== null) {
                        $token = new Token($user);
                        $tokenExists = $tokenRepository->findOneBy([
                            'value' => $token->getValue()
                        ]);
                    }

                    if (!$tokenService->sendForgotPasswordToken($token)) {
                        $this->addFlash(
                            'danger',
                            "Une erreur s'est produite. Merci de recommencer1."
                        );
                        return $this->redirectToRoute('security_password_forgot');
                    }
                    $manager->persist($token);
                    $manager->flush();
                }
                $this->addFlash(
                    'primary',
                    "Un lien de réinitialisation vient de t'être envoyé par Email."
                );

                return $this->redirect($request->getSession()->get('origin_path')?:$this->generateUrl('front_home'));
            }
            $this->addFlash(
                'danger',
                "Une erreur s'est produite. Merci de recommencer2."
            );
        }

        return $this->render('security/forgot-password.html.twig', [
            'last_username' => $this->lastUsername,
            'error' => $this->lastAuthError
        ]);
    }

    #[Route("/recovery/{value}", name: "security_password_recovery")]
    public function passwordRecovery(Token $token, Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $manager): Response
    {
        $user = $token->getUser();

        $form = $this->createForm(PasswordRecoveryType::class);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if ($form['username']->getData() != $user->getUsername()) {
                $form->get('username')->addError(new FormError("Nom d'utlisateur invalide !"));
            }
            if ($form->isValid()) {
                $passwordEncoded = $passwordHasher->hashPassword($user, $form['plainPassword']->getData());
                $user->setPassword($passwordEncoded);

                $manager->remove($token);
                $manager->flush();

                $this->addFlash(
                    'primary',
                    "Ton mot de passe a bien été modifié, tu peux te connecter."
                );
                return $this->redirect($this->generateUrl('front_home') . '#login');
            }

            $this->addFlash(
                'warning',
                "Les données du formulaire sont invalides."
            );
        }
        return $this->render('security/password-recovery.html.twig', [
            'form' => $form->createView(),
            'last_username' => $this->lastUsername,
            'error' => $this->lastAuthError
        ]);
    }
}
