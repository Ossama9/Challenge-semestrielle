<?php

namespace App\Controller\Back;

use App\Entity\Comment;
use App\Form\Comment1Type;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/comment')]
class CommentController extends AbstractController
{
    #[Route('/', name: 'app_comment_index', methods: ['GET'])]
    public function index(
        Request $request,
        CommentRepository $commentRepository,
        PaginatorInterface $paginator,
    ): Response
    {
        $paginatedComments = $paginator->paginate(
            $commentRepository->findAll(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('back/comment/index.html.twig', [
            'comments' => $paginatedComments,
            'page' => 'comment',
        ]);
    }

    #[Route('/{id}', name: 'app_comment_show', methods: ['GET'])]
    public function show(Comment $comment): Response
    {
        return $this->render('back/comment/show.html.twig', [
            'comment' => $comment,
            'page' => 'comment',
        ]);
    }

    #[Route('/{id}/remove/{token}', name: 'app_comment_delete', methods: ['GET'])]
    public function delete(Request $request,
        Comment $comment,
        CommentRepository $commentRepository,
        string $token
    ): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$comment->getId(), $token)) {
            throw $this->createAccessDeniedException();
        }

        $commentRepository->remove($comment, true);
        $this->addFlash('success','Le commentaire a bien été supprimé');

        return $this->redirectToRoute('admin_app_comment_index', [], Response::HTTP_SEE_OTHER);
    }
}
