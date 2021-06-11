<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'security_login';

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;
    private $originUrl;
    private $targetUrl;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request)
    {
        $this->originUrl = $request->getSession()->get('_security.main.target_path');
        if (
            (
                (self::LOGIN_ROUTE  === $request->attributes->get('_route'))
                || preg_match('/^front_/', $request->attributes->get('_route'))

            )
            && $request->isMethod('POST')) {

            if ($request->get('target')) {
                $this->targetUrl = $request->get('target');
            }
            return true;
        }
        /*if (preg_match('/^security_/', $request->attributes->get('_route')) && $request->isMethod('POST')) {
            $this->targetUrl = "home";
            if ($request->get('target')) {
                $this->targetUrl = $request->get('target');
            }
            return true;
        }*/

        $this->targetUrl = $request->attributes->get("_route");
        return false;
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email'], 'enabled' => true]);

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Email could not be found.');
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        if ($request->get('target')) {
            return new RedirectResponse($this->urlGenerator->generate($request->get('target')));
        }

        return new RedirectResponse($request->getUri());
    }

    protected function getLoginUrl()
    {
        $target = empty($this->targetUrl)?[]:["target" => $this->targetUrl];
        return $this->urlGenerator->generate(self::LOGIN_ROUTE, $target);

        $target = empty($this->targetUrl)?"":"?target=" . $this->targetUrl;
        //$this->originUrl?:$this->originUrl = $this->urlGenerator->generate("front_home");
        //$this->originUrl = $this->urlGenerator->generate("front_home");
        //dd($this->originUrl, $this->originUrl . $target ."#login");
        //$target = "";
        return $this->originUrl . $target ."#login";
    }
}
