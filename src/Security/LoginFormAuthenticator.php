<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PasswordUpgradeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    private UserRepository $userRepository;
    private UrlGeneratorInterface $urlGenerator;
    private CsrfTokenManagerInterface $csrfTokenManager;
    private ?string $originUrl;
    private string $targetUrl;

    public function __construct(UserRepository $userRepository, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->userRepository = $userRepository;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    public function supports(Request $request): bool
    {
        $this->originUrl = $request->getSession()->get('origin_path');
        if (
            (
                preg_match('/^front_home/', $request->attributes->get('_route'))
                || preg_match('/^front_tricks-single/', $request->attributes->get('_route'))
                || preg_match('/^security_registration/', $request->attributes->get('_route'))
                || preg_match('/^security_password_forgot/', $request->attributes->get('_route'))
                || preg_match('/^security_password_recovery/', $request->attributes->get('_route'))

            )
            && $request->isMethod('POST') && $request->get('login')) {
            if ($request->get('target')) {
                $this->targetUrl = $request->get('target');
            }
            return true;
        }

        $this->targetUrl = $request->attributes->get("_route");
        return false;
    }

    public function authenticate(Request $request): PassportInterface
    {
        $credentials = $this->getCredentials($request);

        $user = $this->userRepository->findOneBy(['email' => $credentials['email'], 'enabled' => true]);

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Invalid credentials.');
        }

        return new Passport(new UserBadge($credentials['email']), new PasswordCredentials($credentials['password']), [
            new CsrfTokenBadge('authenticate', $credentials['_csrf_token']),
            new PasswordUpgradeBadge($credentials['password'], $this->userRepository)
        ]);
    }

    private function getCredentials(Request $request): array
    {
        $credentials = (array) $request->request->get('login');

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );
        return $credentials;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): RedirectResponse
    {
        if ($request->get('target')) {
            return new RedirectResponse($this->urlGenerator->generate($request->get('target')));
        }

        return new RedirectResponse($request->getUri());
    }

    protected function getLoginUrl(Request $request): string
    {
        $target = empty($this->targetUrl)?"":"?target=" . $this->targetUrl;
        return $this->originUrl . $target ."#login";
    }
}
