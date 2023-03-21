<?php

namespace App\Controller\Back;

use App\Entity\RequestCustomer;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\HotelRepository;
use App\Repository\RequestCustomerRepository;
use Symfony\Component\Mailer\MailerInterface;
use App\Service\Email;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/request', name: 'app_request_')]
class RequestController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(
        Request $request,
        RequestCustomerRepository $requestCustomerRepository,
        PaginatorInterface $paginator,
    ): Response
    {
        $requests = $requestCustomerRepository->findBy(['isApproved' => null]);

        $paginatedRequests = $paginator->paginate(
            $requests,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('back/request/index.html.twig', [
            'requests' => $paginatedRequests,
            'page' => 'request'
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Request $request, RequestCustomer $requestCustomer): Response
    {
        return $this->render('back/request/show.html.twig', [
            'request' => $requestCustomer,
            'page' => 'request'
        ]);
    }

    #[Route('/{id}/accept/{token}', name: 'accept', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function accept(
        Request                   $request,
        RequestCustomer           $requestCustomer,
        UserRepository            $userRepository,
        RequestCustomerRepository $requestCustomerRepository,
        HotelRepository           $hotelRepository,
        ManagerRegistry           $managerRegistry,
        MailerInterface           $mailer,
        string                    $token
    ): Response
    {
        if (!$this->isCsrfTokenValid('accept' . $requestCustomer->getId(), $token)) {
            throw $this->createAccessDeniedException();
        }
        (new Email($mailer))
            ->sendEmailToNotifyCustomer($requestCustomer->getUser()->getEmail());
        $requestCustomer->setIsApproved(1);
        $requestCustomer->getUser()->setIsCustomer(1);
        $requestCustomer->getUser()->setRoles(["ROLE_CUSTOMER"]);
        $requestCustomer->getHotel()->setIsValidated(1);
        $userRepository->save($requestCustomer->getUser(), true);
        $hotelRepository->save($requestCustomer->getHotel(), true);
        $requestCustomerRepository->save($requestCustomer, true);

        return $this->redirectToRoute('admin_app_request_index');

    }

    #[Route('/{id}/decline/{token}', name: 'decline', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function decline(
        Request                   $request,
        RequestCustomer           $requestCustomer,
        UserRepository            $userRepository,
        RequestCustomerRepository $requestCustomerRepository,
        HotelRepository           $hotelRepository,
        MailerInterface           $mailer,
        ManagerRegistry           $managerRegistry,
        string                    $token
    ): Response
    {
        if (!$this->isCsrfTokenValid('decline' . $requestCustomer->getId(), $token)) {
            throw $this->createAccessDeniedException();
        }
        if ($requestCustomer->getUser()->getHotels()->count() === 1) {
            $requestCustomer->getUser()->setIsCustomer(-1);
        }
        (new Email($mailer))
            ->sendEmailToReject($requestCustomer->getUser()->getEmail());
        $requestCustomer->setIsApproved(0);
        $requestCustomer->getHotel()->setIsValidated(0);
        $userRepository->save($requestCustomer->getUser(), true);
        $requestCustomerRepository->save($requestCustomer, true);
        $hotelRepository->save($requestCustomer->getHotel(), true);
        return $this->redirectToRoute('admin_app_request_index');
    }
}
