<?php

namespace App\Handler;

use App\Entity\User;
use App\Entity\Customer;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface as Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserHandler extends AbstractHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * TrickHandler constructor.
     * @param EntityManagerInterface $entityManager
     * @param Serializer $serializer
     */
    public function __construct(EntityManagerInterface $entityManager, Serializer $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;

    }

    /**
     * @param Request $request
     * @param Customer $customer
     */
    public function post (Request $request, Customer $customer) {

        $user = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        // a supprimer quand j'aurais le retour du token
        $user->setCustomer($this->entityManager->getRepository(Customer::class)->findOneBy([]));

        if(!$user){
            dump($request->get('firstname'));
        }
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        if($this->entityManager->getUnitOfWork()->getEntityState($user) == 1){
            return $user;
        }
        return null;



    }
}