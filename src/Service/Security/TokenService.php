<?php


namespace App\Service\Security;


use App\Entity\Token;
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

    public function sendRegistrationToken(Token $token): bool
    {
        $message = (new TemplatedEmail())
            ->to($token->getUser()->getEmail())
            ->subject('SnowTricks - Confirme ton inscritpion')
            ->htmlTemplate('emails/security/registration.html.twig')
            ->context([
                'token' => $token->getValue()
            ]);

        return $this->send($message);
    }

    public function sendForgotPasswordToken(Token $token): bool
    {
        $message = (new TemplatedEmail())
            ->to($token->getUser()->getEmail())
            ->subject('SnowTricks - Réinitialise ton mot de passe')
            ->htmlTemplate('emails/security/forgot-password.html.twig')
            ->context([
                'token' => $token->getValue()
            ]);

        return $this->send($message);
    }

    private function send(TemplatedEmail $message): bool
    {
        try {
            $this->mailer->send($message);
        } catch (TransportExceptionInterface) {
            return false;
        }
        return true;
    }
}