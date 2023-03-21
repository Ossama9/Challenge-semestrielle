<?php

namespace App\Security;

use App\Entity\User as AppUser;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }
    public function checkPreAuth(UserInterface $user): void
    {

        if (!$user instanceof AppUser) {
            return;
        }

        if (!$user->isIsActivated()) {
            $session = $this->requestStack->getSession();
            $session->getFlashBag()->add('error', 'Your user account is not activated.');
            throw new CustomUserMessageAccountStatusException('');
        }
        if ($user->isIsBanned()){
            $session = $this->requestStack->getSession();
            $session->getFlashBag()->add('error', 'Your account is banned.');
            throw new CustomUserMessageAccountStatusException();
        }

    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof AppUser) {
            return;
        }

        // user account is expired, the user may be notified
//        if ($user->isExpired()) {
//            throw new AccountExpiredException('...');
//        }
    }
}
