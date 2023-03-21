<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Announce;

class AnnounceVoter extends Voter
{
    public const EDIT = 'POST_EDIT';
    public const SHOW = 'POST_SHOW';
    public const DELETE = 'POST_DELETE';


    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::SHOW, self::DELETE])
            && $subject instanceof Announce;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
                return in_array('ROLE_CUSTOMER', $user->getRoles()) && $subject->getHotel()->getUser() === $user;
                break;
            case self::SHOW:
            case self::DELETE:
//                dd($subject->getHotel());
                return in_array('ROLE_CUSTOMER', $user->getRoles()) && $subject->getHotel()->getUser() === $user;
                break;
        }

        return false;
    }

}