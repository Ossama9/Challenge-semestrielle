<?php

namespace App\Controller\Back;

use App\Entity\Announce;
use App\Form\AnnounceType;
use App\Repository\AnnounceRepository;
use App\Repository\PaymentRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/announce')]
class AnnounceController extends AbstractController
{
    #[Route('/', name: 'app_announce_index', methods: ['GET'])]
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

        return $this->render('back/announce/index.html.twig', [
            'announces' => $paginatedAnnounce,
            'page' => 'announce'
        ]);
    }

    #[Route('/{id}', name: 'app_announce_show', methods: ['GET'])]
    public function show(Announce $announce): Response
    {
        return $this->render('back/announce/show.html.twig', [
            'announce' => $announce,
            'page' => 'announce'
        ]);
    }

    #[Route('/{id}/remove/{token}', name: 'app_announce_delete', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function delete(Request $request,
        Announce $announce,
        AnnounceRepository $announceRepository,
        ReservationRepository $reservationRepository,
        PaymentRepository $paymentRepository,
        string $token
    ): Response
    {
        if (!$this->isCsrfTokenValid('delete' . $announce->getId(), $token)) {
            throw $this->createAccessDeniedException();
        }

        $announceRepository->remove($announce, true);
        $this->addFlash('success',"L'annonce a bien été supprimé");

        return $this->redirectToRoute('admin_app_announce_index', [], Response::HTTP_SEE_OTHER);
    }
}
