<?php

namespace App\Controller\Customer;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\UserRepository;
use App\Repository\HotelRepository;
use App\Repository\AnnounceRepository;
use App\Repository\CommentRepository;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'default_index')]
    public function index(
        Security $security,
        UserRepository $userRepository,
        HotelRepository $hotelRepository,
        AnnounceRepository $announceRepository,
        CommentRepository $commentRepository
    ): Response
    {
        $hotel = $hotelRepository->findBy(['user' => $this->getUser()]);
        $countHotel = $hotelRepository->count(['user' => $this->getUser()]);
        $countAnnounce = $announceRepository->count(['hotel' => $hotel]);
        $countComment = $commentRepository->count(['hotel' => $hotel]);

        return $this->render('customer/default/index.html.twig', [
            'user' => $security->getUser(),
            'countHotel' => $countHotel,
            'countAnnounce' => $countAnnounce,
            'countComment' => $countComment,
            'page' => 'dashboard'
        ]);
    }
}
