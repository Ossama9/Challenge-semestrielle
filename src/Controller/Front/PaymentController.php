<?php
namespace App\Controller\Front;

use App\Entity\Payment;
use App\Entity\Reservation;
use App\Repository\PaymentRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use App\Service\Email;
use App\Service\PdfService;
use DateTime;
use Stripe\Charge;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/payment', name: 'app_payment')]
class PaymentController extends AbstractController
{
    #[Route('/{id}', name: '', methods: ['GET', 'POST'])]
    public function index(Reservation $reservation): Response
    {
        $price = $reservation->getDiffPrice();
        if ($price == NULL){
            $price = $reservation->getPrice();
        }
        return $this->render('front/payment/index.html.twig', [
            'stripe_key' => $_ENV["STRIPE_KEY"],
            'reservation'=>$reservation,
            'price' => $price,
        ]);
    }
    #[Route('/create-charge/{id}', name: '_charge', methods: ['GET', 'POST'])]
    public function createCharge(Request $request, Reservation $reservation, PaymentRepository $paymentRepository, MailerInterface $mailer, UserRepository $userRepository)
    {
        $price = $reservation->getDiffPrice();
        if ($price == NULL){
            $price = $reservation->getPrice();
        }
        $payment = new Payment();
        $payment->setUser($reservation->getUser());
        $payment->setCreatedAt(new DateTime());
        $payment->setReservation($reservation);
        $payment->setAmount($price);
        Stripe::setApiKey($_ENV["STRIPE_SECRET"]);
        $mail = new Email($mailer);
        $user = $userRepository->findOneBy([
            'email' => $this->getUser()->getEmail(),
        ]);
        Charge::create([
            "amount" => $price * 100,
            "currency" => "eur",
            "source" => $request->request->get('stripeToken'),
            "description" => "Payment Reservation Hotelia"
        ]);
        $mail->sendEmailForReservation($user->getEmail(), $reservation);
        $paymentRepository->save($payment, true);
        $this->addFlash(
            'success',
            'Payment Successful ! a confirmation email has been sent. '
        );

        return $this->redirectToRoute('front_app_reservation_show', ['id'=>$reservation->getId()], Response::HTTP_SEE_OTHER);
    }

}