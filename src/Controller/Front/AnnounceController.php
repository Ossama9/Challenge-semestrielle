<?php

namespace App\Controller\Front;

use App\Entity\Announce;
use App\Entity\Hotel;
use App\Entity\Reservation;
use App\Form\AnnounceType;
use App\Form\ReservationType;
use App\Repository\AnnounceRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/announce')]
class AnnounceController extends AbstractController
{
    #[Route('/', name: 'app_announce_index', options: ['sitemap' => true], methods: ['GET'])]
    public function index(
        Request $request,
        AnnounceRepository $announceRepository,
        PaginatorInterface $paginator
    ): Response
    {
        $paginatedAnnounce = $paginator->paginate(
            $announceRepository->findAll(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('front/announce/index.html.twig', [
            'announces' => $paginatedAnnounce,
        ]);
    }

    #[Route('/search', name: 'default_announce_search', options: ['sitemap' => false], methods: ['POST'])]
    public function propertiesSearch(AnnounceRepository $announceRepository, Request $request): JsonResponse
    {
        $searchValue = $request->request->get('search');
        $searchResults = $announceRepository->search($searchValue);
        $searchResultsArray = [];

        foreach ($searchResults as $announce) {
            $searchResultsArray[] = [
                'getId' => $announce->getId(),
                'image' => $announce->getHotel()->getImage(),
                'name' => $announce->getTitle(),
                'price' => $announce->getPrice(),
                'nbBeds' => $announce->getNumberOfBeds()
            ];
        }

        return new JsonResponse($searchResultsArray);
    }

    #[Route('/{id}', name: 'app_announce_show', methods: ['GET', 'POST'])]
    public function show(Announce $announce, Request $request, ReservationRepository $reservationRepository, UserRepository $userRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userRepository->findOneBy([
                'email' => $this->getUser()->getEmail(),
            ]);
            $reservation->setAnnounce($announce);
            $start = $reservation->getStart();
            $end = $reservation->getEnd();
            $verif = $reservationRepository->findBy(['announce' => $reservation->getAnnounce()]);
            $i = 0;
            if ($start > $end) {
                $i = 2;
            }
            foreach ($verif as $verification) {
                $debut = $verification->getStart();
                $fin = $verification->getEnd();
                if (($start < $debut && $end < $debut) || ($start > $fin && $end > $fin)) {
                } else {
                    $i = 1;
                }
            }
            if ($i == 0) {
                $reservation->setUser($user);
                $reservation->setCreatedAt(new DateTimeImmutable('now'));
                $number_days = date_diff($start, $end)->format('%a');
                $price = ($announce->getPrice()) * $number_days;
                $reservation->setPrice($price);
                $reservationRepository->save($reservation, true);
                return $this->redirectToRoute('front_app_payment', ['id' => $reservation->getId()], Response::HTTP_SEE_OTHER);
            } elseif ($i == 2) {
                $this->addFlash("error", "Please choose the correct start date");
            } else {
                $this->addFlash("error", "Please choose other dates");
            }

        }

        return $this->render('front/announce/show.html.twig', [
            'announce' => $announce,
            'hotel' => $announce->getHotel(),
            'form' => $form,
            'reservation' => $reservation,
        ]);
    }
}
