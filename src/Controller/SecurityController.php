<?php

namespace App\Controller;

use App\Entity\Token;
use App\Entity\User;
use App\Form\RegistrationType;
use App\Repository\TokenRepository;
use App\Repository\UserRepository;
use App\Service\Security\TokenService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private ?string $lastUsername;
    private ?string $lastAuthenticationError;

    public function __construct(AuthenticationUtils $authenticationUtils)
    {
        // get the login error if there is one
        $this->lastAuthenticationError = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $this->lastUsername = $authenticationUtils->getLastUsername();
    }

    /**
     * @Route("/login", name="security_login")
     */
    public function login(Request $request): Response
    {
        if ($this->getUser()) {
            $this->addFlash(
                'primary',
                "Vous êtes déjà connecté."
            );
            return $this->redirect($request->getSession()->get('_security.main.target_path'));
        }

        return $this->redirect($request->getSession()->get('_security.main.target_path') . '#login');
    }

    /**
     * @Route("/logout", name="security_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/registration", name="security_registration")
     */
    public function registration(Request $request, UserRepository $userRepository, TokenRepository $tokenRepository, EntityManagerInterface $manager, UserPasswordEncoderInterface $passwordEncoder, TokenService $tokenService): Response
    {
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

            $userExists = $userRepository->findOneBy([
                'email' => $user->getEmail()
            ]);

            if ($form->isValid()) {
                if ($userExists) {
                    if ($userExists->getEnabled()) {
                        $this->addFlash(
                            'notice',
                            "Cette adresse Email est déjà active. Vous pouvez vous connecter"
                        );
                        return $this->redirect($request->getSession()->get('_security.main.target_path') . '#login');
                    }
                    $oldToken = $tokenRepository->findOneBy([
                        'user' => $userExists
                    ]);
                    if ($oldToken) {
                        $manager->remove($oldToken);
                    }
                    $manager->remove($userExists);
                    $manager->flush();
                }

                $passwordEncoded = $passwordEncoder->encodePassword($user, $user->getPassword());
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

                if ($tokenService->sendRegistrationToken($user, $token)) {
                    $manager->persist($token);
                    $manager->flush();

                    $this->addFlash(
                        'success',
                        "Un Email de validation vient de vous être envoyé."
                    );
                    return $this->redirectToRoute('home');
                }

                $this->addFlash(
                    'danger',
                    "Une erreur s'est produite. Merci de rééssayer ultérieurement"
                );

                return $this->render('security/registration.html.twig', [
                    'form' => $form->createView(),
                    'last_username' => $this->lastUsername,
                    'error' => $this->lastAuthenticationError
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
            'error' => $this->lastAuthenticationError
        ]);
    }

    /**
     * @Route("/registration/confirmation", name="security_registration_confirmation")
     */
    public function registrationConfirmation(): Response
    {
        return $this->render('security/registration-confirmation.html.twig');
    }

    /**
     * @Route("/password/forgot", name="security_password_forgot")
     */
    public function passwordForgot(): Response
    {
        return $this->render('security/password-forgot.html.twig');
    }

    /**
     * @Route("/recovery", name="security_password_recovery")
     */
    public function passwordRecovery(): Response
    {
        return $this->render('security/password-recovery.html.twig');
    }
}
