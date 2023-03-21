<?php

namespace App\Controller\Customer;


use App\Entity\Comment;
use App\Entity\Hotel;
use App\Repository\CommentRepository;
use App\Repository\HotelRepository;
use Doctrine\DBAL\ArrayParameterType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;

#[Route('/comment'), IsGranted('ROLE_CUSTOMER')]
class CommentController extends AbstractController
{
    #[Route('/', name: 'app_comment_index', methods: ['GET'])]
    public function index(
        Request $request,
        HotelRepository $hotelRepository,
        PaginatorInterface $paginator,
    ): Response
    {
        $paginatedHotels = $paginator->paginate(
            $hotelRepository->findBy(['user' => $this->getUser()]),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('customer/comment/index.html.twig', [
            'hotels' => $paginatedHotels,
            'page' => 'comment'
        ]);
    }

    #[Route('/{id}/remove/{token}', name: 'app_comment_delete', methods: ['GET'])]
    public function remove(Comment $comment, CommentRepository $commentRepository, string $token): Response
    {
        if (!$this->isCsrfTokenValid('remove' . $comment->getId(), $token)) {
            throw $this->createAccessDeniedException();
        }
        $commentRepository->remove($comment, true);
        $this->addFlash("info","Le commentaire a bien été supprimé");
        return $this->redirectToRoute('customer_app_comment_index');

    }
}
