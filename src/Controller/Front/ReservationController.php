<?php

namespace App\Controller\Front;

use App\Entity\Announce;
use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\PaymentRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use App\Security\Voter\ReservationVoter;
use App\Service\PdfService;
use DateTimeImmutable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reservation'),IsGranted('ROLE_USER')]
class ReservationController extends AbstractController
{
    #[Route('/', name: 'app_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy([
            'email' => $this->getUser()->getEmail(),
        ]);
        return $this->render('front/reservation/index.html.twig', [
            'reservations' => $reservationRepository->findBy(['user'=>$user->getId()]),
        ]);
    }

    #[Route('/pdf/{id}', name: 'reservation.pdf')]
    #[IsGranted(ReservationVoter::SHOW, 'reservation')]
    public function generatePdfReservation(Reservation $reservation, PdfService $pdf): Response
    {
        $id = $reservation->getId();
        $html = $this->render('front/reservation/pdf.html.twig', ['id'=>$id,'reservation'=>$reservation]);
        $pdf->showPdfFile($html);
    }
    #[Route('/{id}', name: 'app_reservation_show', methods: ['GET'])]
    #[IsGranted(ReservationVoter::SHOW, 'reservation')]
    public function show(Reservation $reservation): Response
    {
        $announce = $reservation->getAnnounce();
        return $this->render('front/reservation/show.html.twig', [
            'reservation' => $reservation,
            'announce' => $announce,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    #[IsGranted(ReservationVoter::EDIT, 'reservation')]
    public function edit(Request $request, Reservation $reservation,UserRepository $userRepository, ReservationRepository $reservationRepository): Response
    {
        $i = 0;
        $old_price = $reservation->getPrice();
        $old_start = $reservation->getStart();
        $old_end = $reservation->getEnd();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $start = $reservation->getStart();
            $end = $reservation->getEnd();
            if ($start>$end){
                $this->addFlash("error","Please choose the correct start date");
            } else {
                $verif = $reservationRepository->findBy(['announce'=>$reservation->getAnnounce()]);
                $user = $userRepository->findOneBy([
                    'email' => $this->getUser()->getEmail(),
                ]);
                foreach ($verif as $verification){
                    if ($verification->getUser() == $user->getEmail() || $verification->getId() == $reservation->getId()){}
                    else{
                        $debut= $verification->getStart();
                        $fin  = $verification->getEnd();
                        if (($start<$debut && $end<$debut) || ($start>$fin && $end>$fin)){}
                        else{
                            $i= 1;
                        }
                    }
                }
                if ($i==1){
                    $this->addFlash("error","Please choose other dates");
                }
                else{
                    $announce = $reservation->getAnnounce();
                    $new_number_days = date_diff($start,$end)->format('%a');
                    $old_number_days = date_diff($old_start,$old_end)->format('%a');
                    $diff_days = $new_number_days - $old_number_days;
                    $a_payer = ($announce->getPrice())*$diff_days ;
                    $price = $a_payer + $old_price;
                    $reservation->setPrice($price);
                    $reservation->setDiffPrice($a_payer);
                    $reservation->setUpdatedAt(new DateTimeImmutable('now'));
                    $reservationRepository->save($reservation, true);
                    if ($diff_days<0){
                        $this->addFlash(
                            'success',
                            'You will receive a refund of'. ($a_payer)*(-1).'! a confirmation email has been sent.'
                        );
                        return $this->redirectToRoute('front_app_reservation_index', [], Response::HTTP_SEE_OTHER);
                    }
                    elseif ($diff_days>0){
                        $this->addFlash(
                            'success',
                            'You must pay the change fee '. ($a_payer).' € !'
                        );
                        return $this->redirectToRoute('front_app_payment', ['id'=>$reservation->getId()], Response::HTTP_SEE_OTHER);
                    }
                    else{
                        $this->addFlash(
                            'success',
                            'You have no fees to pay'
                        );
                        return $this->redirectToRoute('front_app_reservation_index', [], Response::HTTP_SEE_OTHER);
                    }
                }
            }
        }

        return $this->render('front/reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_delete', methods: ['POST'])]
    #[IsGranted(ReservationVoter::DELETE, 'reservation')]
    public function delete(Request $request, Reservation $reservation, ReservationRepository $reservationRepository, PaymentRepository $paymentRepository): Response
    {
        $delete = $paymentRepository->findOneBy(['reservation'=>$reservation->getId()]);
        $paymentRepository->remove($delete);
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->request->get('_token'))) {
            $reservationRepository->remove($reservation, true);
        }
        $this->addFlash(
            'success',
            'You will receive a refund in 7 Days, mail has sent !'
        );
        return $this->redirectToRoute('front_app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }

}
