<?php

namespace App\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\UserRepository;
use App\Repository\HotelRepository;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'default_index')]
    public function index(
        Security $security,
        UserRepository $userRepository,
        HotelRepository $hotelRepository
    ): Response
    {
        $countWantToBeCustomer = $userRepository->count(['isCustomer' => 0]);
        $countCustomer = $userRepository->count(['isCustomer' => 1]);
        $countHotel = $hotelRepository->count();
        return $this->render('back/default/index.html.twig', [
            'user' => $security->getUser(),
            'countWantToBeCustomer' => $countWantToBeCustomer,
            'countCustomer' => $countCustomer,
            'countHotel' => $countHotel,
            'page' => 'dashboard'
        ]);
    }
}
