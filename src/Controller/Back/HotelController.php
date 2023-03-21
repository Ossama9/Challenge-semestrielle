<?php

namespace App\Controller\Back;

use App\Entity\Hotel;
use App\Entity\Announce;
use App\Form\HotelType;
use App\Repository\CommentRepository;
use App\Repository\HotelRepository;
use App\Repository\AnnounceRepository;
use App\Repository\ReservationRepository;
use App\Repository\PaymentRepository;
use App\Repository\RequestCustomerRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/hotel')]
class HotelController extends AbstractController
{
    #[Route('/', name: 'app_hotel_index', methods: ['GET'])]
    public function index(
        Request $request,
        HotelRepository $hotelRepository,
        PaginatorInterface $paginator
    ): Response
    {
        $paginatedHotel = $paginator->paginate(
            $hotelRepository->findAll(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('back/hotel/index.html.twig', [
            'hotels' => $paginatedHotel,
            'page' => 'hotel',
        ]);
    }

    #[Route('/{id}', name: 'app_hotel_show', methods: ['GET'])]
    public function show(Hotel $hotel): Response
    {
        return $this->render('back/hotel/show.html.twig', [
            'hotel' => $hotel,
            'page' => 'hotel',
        ]);
    }

    #[Route('/{id}/remove/{token}', name: 'app_hotel_delete', methods: ['GET'])]
    public function delete(
        Request $request,
        Hotel $hotel,
        HotelRepository $hotelRepository,
        AnnounceRepository $announceRepository,
        CommentRepository $commentRepository,
        ReservationRepository $reservationRepository,
        PaymentRepository $paymentRepository,
        RequestCustomerRepository $requestCustomerRepository,
        string $token
    ): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $hotel->getId(), $token)) {
            throw $this->createAccessDeniedException();
        }

        $hotelRepository->remove($hotel, true);
        $this->addFlash("success","L'hôtel a bien été supprimé");

        return $this->redirectToRoute('admin_app_hotel_index', [], Response::HTTP_SEE_OTHER);
    }
}
