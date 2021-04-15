<?php


namespace App\Controller;


use App\Entity\Customer;
use App\Entity\User;
use App\Handler\QueryParamsHandler;
use App\Handler\UserHandler;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations\Swagger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security as SecurityAlias;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\Serializer\SerializerInterface as Serializer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Hateoas\HateoasBuilder;
use Swagger\Annotations as SWG;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security as docSecurity;


/**
 * Class UserController
 * @package App\Controller
 * @Route("/api/users")
 */
class UserController extends AbstractController
{
    /**
     * @var SecurityAlias
     */
    private $security;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(SecurityAlias $security, Serializer $serializer )
    {
        $this->security = $security;
        $this->serializer = $serializer;

    }

    /**
     * @Route(name="api_users_listing", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Returns all customer's user in a listing",
     *
     *     @SWG\Schema(
     *             required={"id", "lastname", "firstname"},
     *             @SWG\Property(property="id", type="string", description="Identifier"),
     *             @SWG\Property(property="lastname", type="string", description="user's lastname"),
     *             @SWG\Property(property="firstname", type="string", description="user's lastname"),
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Bad request",
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Token expired",
     * )
     * @SWG\Response(
     *     response=404,
     *     description="you can't access to this result",
     * )
     * @DocSecurity(name="Bearer")
     * @param UserRepository $userRepository
     * @param QueryParamsHandler $handler
     * @return JsonResponse
     */
    public function listing(UserRepository $userRepository,QueryParamsHandler $handler): JsonResponse
    {
        $customer = $this->security->getUser();


        $user = $userRepository->search($customer,$handler->getLimit(),$handler->getOffset());
        $serializableResults = $user->getIterator()->getArrayCopy();
        $data =  $this->serializer->serialize($serializableResults,'json',SerializationContext::create()->setGroups(array('listing', 'Default')));
        return new JsonResponse($data,
        JsonResponse::HTTP_OK,
        [],
        true);
    }

    /**
     * @Route("/{id}", name="api_users_item", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Returns the user you ask",
     *
     *     @SWG\Schema(
     *             @SWG\Property(property="id", type="string", description="Identifier"),
     *             @SWG\Property(property="lastname", type="string", description="user's lastname"),
     *             @SWG\Property(property="firstname", type="string", description="user's lastname"),
     *             @SWG\Property(property="Products", type="relation", description="user's products"),
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Bad request",
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Token expired",
     * )
     * @SWG\Response(
     *     response=404,
     *     description="you can't access to this result",
     * )
     * @DocSecurity(name="Bearer")
     * @param User $user
     * @return JsonResponse
     * @IsGranted ("edit",subject="user")
     */
    public function item(User $user): JsonResponse
    {
        $customer = $this->security->getUser();
        dump($customer);
        $data =  $this->serializer->serialize($user,'json',SerializationContext::create()->setGroups(array('detail')));
        return new JsonResponse($data,
            JsonResponse::HTTP_OK,
            [],
            true);
    }

    /**
     * @Route(name="api_users_item_add", methods={"POST"})
     * @SWG\Response(
     *     response=200,
     *     description="create a user",
     *
     *     @SWG\Schema(
     *             required={"lastname", "firstname"},
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Bad request",
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Token expired",
     * )
     * @SWG\Response(
     *     response=404,
     *     description="you can't access to this result",
     * )
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @param UserHandler $handler
     * @return JsonResponse
     */
    public function post(Request $request,  EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, UserHandler $handler): JsonResponse
    {
        $customer = $this->security->getUser();
        $user =  $handler->post($request, $customer);

        if($user !== NULL){
            $data =  $this->serializer->serialize($user,'json',SerializationContext::create()->setGroups(array('listing')));
            return new JsonResponse(
                $data,
                JsonResponse::HTTP_CREATED,
                ["Location" => $urlGenerator->generate("api_users_item", ["id" => $user->getId()])],
                true);return new JsonResponse(
                $data,
                JsonResponse::HTTP_CREATED,
                ["Location" => $urlGenerator->generate("api_users_item", ["id" => $user->getId()])],
                true);

        }
    }
    //204
    /**
     * @Route("/{id}",name="api_users_item_modify", methods={"PUT"})*
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function put(User $user, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $customer = $this->security->getUser();
        /** @var $user User */
        $replacement = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        /*a passer dans un handler avec les parametre suivant : l'objett, la deserialisation, les champs a modifier*/
        $user->setLastname($replacement->getLastname());
        $user->setfirstname($replacement->getFirstname());
        // a supprimer quand j'aurais le retour du token
        $user->setCustomer($entityManager->getRepository(Customer::class)->findOneBy([]));

        $entityManager->flush();
        $data = $this->serializer->serialize($user, 'json', SerializationContext::create()->setGroups(array('listing')));


        return new JsonResponse(
            null,
            JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id}",name="api_users_item_delete", methods={"DELETE"})
     *      * @SWG\Response(
     *     response=200,
     *     description="update a user",
     *
     *     @SWG\Schema(
     *             required={"lastname", "firstname"},
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Bad request",
     * )
     * @SWG\Response(
     *     response=401,
     *     description="Token expired",
     * )
     * @SWG\Response(
     *     response=404,
     *     description="you can't access to this result",
     * )
     * @param User $user
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function delete(User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        $customer = $this->security->getUser();
        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(
            null,
            JsonResponse::HTTP_NO_CONTENT);
    }
}