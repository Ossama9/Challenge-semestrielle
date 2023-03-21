<?php

namespace App\Controller\Customer;

use App\Entity\Announce;
use App\Form\AnnounceType;
use App\Repository\AnnounceRepository;
use App\Repository\HotelRepository;
use App\Repository\UserRepository;
use App\Security\Voter\AnnounceVoter;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/announce'), IsGranted('ROLE_CUSTOMER')]
class AnnounceController extends AbstractController
{
    #[Route('/', name: 'app_announce_index', methods: ['GET'])]
    public function index(
        Request $request,
        HotelRepository $hotelRepository,
        AnnounceRepository $announceRepository,
        PaginatorInterface $paginator
    ): Response
    {
        $my_hotels = $hotelRepository->findBy(['user' => $this->getUser()]);
        $annonces = [];

        foreach ($my_hotels as $my_hotel) {
            $annonces += $announceRepository->findBy(['hotel' => $my_hotel]);
        }

        $paginatedAnnounce = $paginator->paginate(
            $annonces,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('customer/announce/index.html.twig', [
            'announces' => $paginatedAnnounce,
            'page' => 'announce'
        ]);
    }

    #[Route('/new', name: 'app_announce_new', methods: ['GET', 'POST'])]
    public function new(Request $request, AnnounceRepository $announceRepository, UserInterface $user, UserRepository $userRepository): Response
    {
        $announce = new Announce();
        $form = $this->createForm(AnnounceType::class, $announce);
        $form->handleRequest($request);
        $user = $userRepository->findOneBy([
            'email' => $this->getUser()->getEmail(),
        ]);
        if ($form->isSubmitted() && $form->isValid()) {
            $announce->setCreatedAt(new DateTime());
            $announce->setUpdatedAt(new DateTime());
            $announceRepository->save($announce, true);

            return $this->redirectToRoute('customer_app_announce_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('customer/announce/new.html.twig', [
            'announce' => $announce,
            'form' => $form,
            'page' => 'announce'
        ]);
    }

    #[Route('/{id}', name: 'app_announce_show', methods: ['GET'])]
    #[IsGranted(AnnounceVoter::SHOW, 'announce')]
    public function show(Announce $announce): Response
    {
        return $this->render('customer/announce/show.html.twig', [
            'announce' => $announce,
            'page' => 'announce'
        ]);
    }

    #[Route('/{id}/edit', name: 'app_announce_edit', methods: ['GET', 'POST'])]
    #[IsGranted(AnnounceVoter::EDIT, 'announce')]
    public function edit(Request $request, Announce $announce, AnnounceRepository $announceRepository): Response
    {
        $form = $this->createForm(AnnounceType::class, $announce);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $announceRepository->save($announce, true);

            return $this->redirectToRoute('customer_app_announce_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('customer/announce/edit.html.twig', [
            'announce' => $announce,
            'form' => $form,
            'page' => 'announce'
        ]);
    }

    #[Route('/{id}/remove/{token}', name: 'app_announce_delete', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted(AnnounceVoter::DELETE, 'announce')]
    public function delete(Request $request,
        Announce $announce,
        AnnounceRepository $announceRepository,
        string $token
    ): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $announce->getId(), $token)) {
            throw $this->createAccessDeniedException();
        }

        $announceRepository->remove($announce, true);
        $this->addFlash('success',"L'annonce a bien été supprimé");

        return $this->redirectToRoute('customer_app_announce_index', [], Response::HTTP_SEE_OTHER);
    }
}
