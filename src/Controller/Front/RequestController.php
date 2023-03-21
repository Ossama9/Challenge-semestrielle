<?php

namespace App\Controller\Front;

use App\Entity\Hotel;
use App\Entity\RequestCustomer;
use App\Form\HotelType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\HotelRepository;
use App\Repository\RequestCustomerRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Twig\Error\Error;

#[Route('/request', name: 'app_request_'), IsGranted('ROLE_USER')]
class RequestController extends AbstractController
{
    #[Route('/show', name: 'show')]
    public function show(RequestCustomerRepository $requestCustomerRepository): Response
    {
        $requests = $requestCustomerRepository->findBy(['user' => $this->getUser()->getId()]);

        return $this->render('front/request/show.html.twig', [
            'requests' => $requests,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, UserRepository $userRepository, HotelRepository $hotelRepository, RequestCustomerRepository $requestCustomerRepository, SluggerInterface $slugger): Response
    {
        $user = $userRepository->findOneBy([
            'email' => $this->getUser()->getEmail(),
        ]);
        $hotel = new Hotel();
        $requestCustomer = new RequestCustomer();

        $form = $this->createForm(HotelType::class, $hotel);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();
            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();
                $pathFile = '../../../uploads/image_hotel/' . $newFilename;
                try {
                    $image->move(
                        $this->getParameter('image_hotel'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    //Error 500
                    $response = new Response();
                    $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
                }
                $hotel->setImage($pathFile);
            }
            $hotel->setUser($user);
            $hotel->setCreatedAt(new DateTime());
            $hotel->setUpdatedAt(new DateTime());
            $user->setIsCustomer(0);
            $requestCustomer->setUser($user);
            $requestCustomer->setHotel($hotel);
            $requestCustomer->setCreatedAt(new DateTime());
            $hotelRepository->save($hotel, true);
            $requestCustomerRepository->save($requestCustomer, true);
            $userRepository->save($user, true);

            return $this->redirectToRoute('front_app_request_show');
        }

        return $this->render('front/request/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
