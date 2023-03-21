<?php

namespace App\Service;


use App\Entity\Reservation;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class Email{
    public \Symfony\Component\Mime\Email $email;
    public MailerInterface $mailer;
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
        $this->email = new \Symfony\Component\Mime\Email();
        $this->email->from("maxime.pietrucci@gmail.com");
        return $this;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendEmailForRegister(string $to, string $token): void
    {
        $template = file_get_contents("../templates/email/register.html.twig");
        $link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]/validate/{$token}";
        $content = str_replace('{{ doubleoptin }}', $link, $template);
        $this->email->to($to)
            ->subject("Bienvenue sur notre site")
            ->html($content);
        $this->sendEmail();
    }
    /**
     * @throws TransportExceptionInterface
     */
    public function sendEmailToNotifyCustomer(string $to): void
    {
        $template = file_get_contents("../templates/email/notify-accept.html.twig");
        $this->email->to($to)
            ->subject("Demande approuvée")
            ->html($template);
        $this->sendEmail();
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendEmailToReject(string $to): void
    {
        $template = file_get_contents("../templates/email/notify-deny.html.twig");
        $this->email->to($to)
            ->subject("Demande refusée")
            ->html($template);
        $this->sendEmail();
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendEmail(): void
    {
        $this->mailer->send($this->email);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendEmailForReservation(string $to, Reservation $reservation): void
    {
        $template = file_get_contents("../templates/email/reservation.html.twig");
        $content = str_replace('{{ reservation_start }}', $reservation->getStart()->format('d/m/Y'), $template);
        $content = str_replace('{{ reservation_end }}', $reservation->getEnd()->format('d/m/Y'), $content);
        $content = str_replace('{{ reservation_price }}', $reservation->getPrice(), $content);
        $content = str_replace('{{ reservation_name }}', $reservation->getName(), $content);
        $content = str_replace('{{ reservation_price }}', $reservation->getPrice(), $content);
        $content = str_replace('{{ reservation_pdf }}', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . "://{$_SERVER['HTTP_HOST']}/reservation/pdf/{$reservation->getId()}", $content);
        $this->email->to($to)
            ->subject("Confirmation of your reservation for {$reservation->getAnnounce()}")
            ->html($content);
        $this->sendEmail();
    }


}
