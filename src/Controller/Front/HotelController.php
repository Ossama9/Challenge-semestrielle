<?php

namespace App\Controller\Front;

use App\Entity\Announce;
use App\Entity\Comment;
use App\Entity\Hotel;
use App\Form\CommentType;
use App\Repository\AnnounceRepository;
use App\Repository\CommentRepository;
use App\Repository\HotelRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/hotel')]
class HotelController extends AbstractController
{
    #[Route('/', name: 'app_hotel_index', options: ['sitemap' => true], methods: ['GET'])]
    public function index(
        Request            $request,
        HotelRepository    $hotelRepository,
        PaginatorInterface $paginator
    ): Response
    {
        $paginatedHotel = $paginator->paginate(
            $hotelRepository->findBy(["isValidated" => true]),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('front/hotel/index.html.twig', [
            'hotels' => $paginatedHotel,
        ]);
    }

    #[Route('/search', name: 'default_hotel_search', options: ['sitemap' => false], methods: ['POST'])]
    public function propertiesSearch(HotelRepository $hotelRepository, Request $request): JsonResponse
    {
        $searchValue = $request->request->get('search');
        $searchResults = $hotelRepository->search($searchValue);
        $searchResultsArray = [];

        foreach ($searchResults as $hotel) {
            $searchResultsArray[] = [
                'getId' => $hotel->getId(),
                'name' => $hotel->getName(),
                'city' => $hotel->getVille(),
                'note' => $hotel->getNote(),
                'country' => $hotel->getCountry(),
                'image' => $hotel->getImage(),
            ];
        }

        return new JsonResponse($searchResultsArray);
    }

    #[Route('/{id}', name: 'app_hotel_show', methods: ['GET', 'POST'])]
    public function show(Hotel $hotel, HotelRepository $hotelRepository, ReservationRepository $reservationRepository, CommentRepository $avisRepository, Request $request, UserRepository $userRepository): Response
    {
        $total_note = $hotel->getNote();
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        $exists = false;
        if ($this->getUser()){
            $reservations = $reservationRepository->findBy(['user' => $this->getUser()->getId()]);
            foreach ($reservations as $reservation) {
                if ($reservation->getAnnounce()->getHotel()->getId() === $hotel->getId()) {
                    $exists = true;
                }
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->getUser()) {
                return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
            }
            $user = $userRepository->findOneBy([
                'email' => $this->getUser()->getEmail(),
            ]);
            $comment->setUser($user);
            $comment->setHotel($hotel);
            $comment->setCreatedAt(new DateTime());
            $new_note = $comment->getNote();
            $nb_note = $avisRepository->getNbNote($hotel->getId());
            $total_old_note = $avisRepository->getTotalNote($hotel->getId());
            $total_note = ($new_note + $total_old_note) / ($nb_note + 1);
            $hotel->setNote($total_note);
            $avisRepository->save($comment, true);
            $hotelRepository->save($hotel, true);
            $this->addFlash('success', 'Votre commentaire a bien été ajouté');
            return $this->redirectToRoute('front_app_hotel_show', ['id' => $hotel->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/hotel/show.html.twig', [
            'comment' => $comment,
            'form' => $form,
            'hotel' => $hotel,
            'comments' => $avisRepository->findBy(array('hotel' => $hotel->getId())),
            'announces' => $hotel->getAnnounces(),
            'total_note' => $total_note,
            'exists'=>$exists
        ]);
    }
}
