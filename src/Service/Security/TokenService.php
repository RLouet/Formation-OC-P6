<?php


namespace App\Service\Security;


use App\Entity\Token;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class TokenService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendRegistrationToken(User $user, Token $token): bool
    {
        $message = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('SnowTricks - Confirmez votre inscritpion')
            ->htmlTemplate('emails/security/registration.html.twig')
            ->context([
                'token' => $token->getValue()
            ]);

        try {
            $this->mailer->send($message);
        } catch (TransportExceptionInterface $e) {
            return false;
        }
        return true;
    }
}