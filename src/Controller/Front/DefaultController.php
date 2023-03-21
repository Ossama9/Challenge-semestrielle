<?php

namespace App\Controller\Front;

use App\Repository\CommentRepository;
use App\Repository\HotelRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
     #[Route('/', name: 'default_index', options: ['sitemap' => true])]
    public function index(HotelRepository $hotelRepository, CommentRepository $commentRepository): Response
    {
        $hotels = $hotelRepository->sortByRating();
        $hotelsNote = [];
        foreach ($hotels as $hotel){
            $hotelsNote[] = $commentRepository->getNbNote($hotel->getId());
        }
        return $this->render('front/default/index.html.twig', [
            'hotels' => $hotels,
            'nbNotes' => $hotelsNote
        ]);
    }

    #[Route('/about-us', name: 'default_about-us', options: ['sitemap' => true])]
    public function contact(): Response
    {
        return $this->render('front/default/about.html.twig');
    }

    #[Route('/privacy', name: 'default_privacy', options: ['sitemap' => true])]
    public function privacy(): Response
    {
        return $this->render('front/default/privacy.html.twig');
    }
}
