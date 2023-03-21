<?php

namespace App\Controller\Customer;

use App\Entity\Comment;
use App\Entity\Hotel;
use App\Form\CommentType;
use App\Form\HotelType;
use App\Repository\CommentRepository;
use App\Repository\AnnounceRepository;
use App\Repository\HotelRepository;
use App\Repository\RequestCustomerRepository;
use App\Repository\UserRepository;
use App\Security\Voter\HotelVoter;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\SecurityBundle\Security as SecurityBundle;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/hotel'), IsGranted('ROLE_CUSTOMER')]
class HotelController extends AbstractController
{
    #[Route('/', name: 'app_hotel_index', methods: ['GET'])]
    public function index(
        Request $request,
        HotelRepository $hotelRepository, 
        SecurityBundle $security,
        PaginatorInterface $paginator
    ): Response
    {
        $paginatedHotel = $paginator->paginate(
            $hotelRepository->findBy(['user' => $security->getUser(),'isValidated'=>1]),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('customer/hotel/index.html.twig', [
            'hotels' => $paginatedHotel,
            'page' => 'hotel',
        ]);
    }

    #[Route('/new', name: 'app_hotel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, HotelRepository $hotelRepository, SluggerInterface $slugger): Response
    {

        $hotel = new Hotel();
        $form = $this->createForm(HotelType::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hotel->setUser($this->getUser());
            $hotel->setCreatedAt(new DateTime());
            $hotel->setUpdatedAt(new DateTime());
            $hotel->setIsValidated(1);
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
                }
                $hotel->setImage($pathFile);
            }

            $hotelRepository->save($hotel, true);

            return $this->redirectToRoute('customer_app_hotel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('customer/hotel/new.html.twig', [
            'hotel' => $hotel,
            'form' => $form,
            'page' => 'hotel',
        ]);
    }

    #[Route('/{id}', name: 'app_hotel_show', methods: ['GET', 'POST'])]
    #[IsGranted(HotelVoter::SHOW, 'hotel')]
    public function show(Hotel $hotel, HotelRepository $hotelRepository, CommentRepository $avisRepository, Request $request, UserRepository $userRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
        }
        $total_note = $hotel->getNote();
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userRepository->findOneBy([
                'email' => $this->getUser()->getEmail(),
            ]);
            $comment->setUser($user);
            $comment->setHotel($hotel);
            $new_note = $comment->getNote();
            $nb_note = $avisRepository->getNbNote();
            $total_old_note = $avisRepository->getTotalNote();
            $total_note = ($new_note + $total_old_note) / ($nb_note + 1);
            $hotel->setNote($total_note);
            $avisRepository->save($comment, true);
            $hotelRepository->save($hotel, true);
        }

        return $this->render('customer/hotel/show.html.twig', [
            'comment' => $comment,
            'form' => $form,
            'hotel' => $hotel,
            'comments' => $avisRepository->findBy(array('hotel' => $hotel->getId())),
            'total_note' => $total_note,
            'page' => 'hotel',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_hotel_edit', methods: ['GET', 'POST'])]
    #[IsGranted(HotelVoter::EDIT, 'hotel')]
    public function edit(Request $request, Hotel $hotel, HotelRepository $hotelRepository, SluggerInterface $slugger): Response
    {
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
                }
                $hotel->setImage($pathFile);
            }
            $hotelRepository->save($hotel, true);

            return $this->redirectToRoute('customer_app_hotel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('customer/hotel/edit.html.twig', [
            'hotel' => $hotel,
            'form' => $form,
            'page' => 'hotel'
        ]);
    }

    #[Route('/{id}/remove/{token}', name: 'app_hotel_delete', methods: ['GET'])]
    #[IsGranted(HotelVoter::DELETE, 'hotel')]
    public function delete(
        Request $request,
        Hotel $hotel,
        HotelRepository $hotelRepository,
        AnnounceRepository $announceRepository,
        CommentRepository $commentRepository,
        RequestCustomerRepository $requestCustomerRepository,
        string $token
    ): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $hotel->getId(), $token)) {
            throw $this->createAccessDeniedException();
        }

        $hotelRepository->remove($hotel, true);
        $this->addFlash("success","L'hôtel a bien été supprimé");

        return $this->redirectToRoute('customer_app_hotel_index', [], Response::HTTP_SEE_OTHER);
    }
}
