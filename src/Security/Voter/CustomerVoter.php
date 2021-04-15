<?php

namespace App\Security\Voter;


use App\Entity\User;
use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CustomerVoter extends Voter
{
    public const SHOW = 'show';

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, $subject)
    {
        if (!$subject instanceof Customer) {
            return false;
        }

        if (!in_array($attribute, [self::SHOW])) {
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
            case self::SHOW:
                return $customer === $subject;
                break;
        }
    }
}