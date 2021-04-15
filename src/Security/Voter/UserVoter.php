<?php

namespace App\Security\Voter;


use App\Entity\User;
use App\Entity\Customer;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    public const EDIT = 'edit';

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, $subject)
    {
        if (!$subject instanceof User) {
            return false;
        }

        if (!in_array($attribute, [self::EDIT])) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param Customer $subject
     * @param TokenInterface $token
     * @return bool|void
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        /** @var Customer $customer */
        $customer = $token->getUser();

        switch ($attribute) {
            case self::EDIT:
                return $customer === $subject->getCustomer();
                break;
        }
    }
}