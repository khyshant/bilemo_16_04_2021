<?php


namespace App\Controller;


use App\Entity\Customer;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security as SecurityAlias;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\Serializer\SerializerInterface as Serializer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Swagger\Annotations as SWG;
use Hateoas\Configuration\Route as HateoasRoute;
use Hateoas\Representation\Factory\PagerfantaFactory;


/**
 * Class ProductControllerController
 * @package App\Controller
 * @Route("/api/products")
 */
class ProductController extends AbstractController
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
     * @Route(name="api_products_listing", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Returns all products in a listing",
     *
     *     @SWG\Schema(
     *             @SWG\Property(property="id", type="string", description="Identifier"),
     *             @SWG\Property(property="description", type="string", description="product's description"),
     *             @SWG\Property(property="brand", type="string", description="product's brand"),

     *     )
     * )
     * @SWG\Parameter(
     *         name="page",
     *         in="query",
     *         type="integer",
     *         description="the page you want",
     *         required=false,
     *         default=1
     *     ),
     *     @SWG\Parameter(
     *         name="limit",
     *         in="query",
     *         type="integer",
     *         description="the number of results per page",
     *         required=false,
     *         default=1
     *     ),
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
     * @param ProductRepository $productRepository
     * @return JsonResponse
     */
    public function listing(ProductRepository $productRepository, Request $request): JsonResponse
    {
        $page = $request->get('page') ?? 1;
        $numPerPage = $request->get('limit') ?? 10;

        $customer = $this->security->getUser();
        $product = $productRepository->findAll();

        $data =  $this->serializer->serialize($product,'json',SerializationContext::create()->setGroups(array('listing')));
        return new JsonResponse($data,
        JsonResponse::HTTP_OK,
        [],
        true);
        $numPages = ceil (count($collection) / $numPerPage);

        $filter = new CollectionFilter();
        $filter->getPaginationFilter()
            ->setNumPerPage(1)
            ->setPage($page);

        $collection = $filter->filterArrayResults($collection->toArray());

        $halCollection = new CollectionRepresentation(
            $collection,
            'employees',
            'employees'
        );

        $pager = new PaginatedRepresentation(
            $halCollection,
            'test',
            array(),
            $page,
            $numPerPage,
            $numPages
        );

        return $pager;
    }

    /**
     * @Route("/{id}", name="api_Products_item", methods={"GET"})
     * @IsGranted("ROLE_USER")
     * @SWG\Response(
     *     response=200,
     *     description="Returns a products",
     *
     *     @SWG\Schema(
     *             @SWG\Property(property="id", type="string", description="Identifier"),
     *             @SWG\Property(property="description", type="string", description="product's description"),
     *             @SWG\Property(property="brand", type="string", description="product's brand"),

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
     * @param Product $product
     * @return JsonResponse
     */
    public function item(Product $product): JsonResponse
    {
        $customer = $this->security->getUser();
        dump($customer);
        $data =  $this->serializer->serialize($product,'json',SerializationContext::create()->setGroups(array('detail')));
        return new JsonResponse($data,
            JsonResponse::HTTP_OK,
            [],
            true);
    }

    /**
     * @Route(name="api_Products_item_add", methods={"POST"})
     * @param Request $request
     * @SWG\Response(
     *     response=200,
     *     description="create a product",
     *
     *     @SWG\Schema(
     *             @SWG\Property(property="id", type="string", description="Identifier"),
     *             @SWG\Property(property="description", type="string", description="product's description"),
     *             @SWG\Property(property="brand", type="string", description="product's brand"),

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
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     */
    public function post(Request $request,  EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $customer = $this->security->getUser();
        dump($customer);
        /** @var $product Product*/
        $product = $this->serializer->deserialize($request->getContent(), Product::class, 'json');
        // a supprimer quand j'aurais le retour du token
        $entityManager->persist($product);
        $entityManager->flush();
        $data =  $this->serializer->serialize($product,'json',SerializationContext::create()->setGroups(array('listing')));
        return new JsonResponse(
            $data,
            JsonResponse::HTTP_CREATED,
            ["Location" => $urlGenerator->generate("api_Products_item", ["id" => $Product->getId()])],
            true);
    }
    /**
     * @Route("/{id}",name="api_Products_item_modify", methods={"PUT"})*
     * @param Request $request
    @SWG\Response(
     *     response=200,
     *     description="update a product",
     *
     *     @SWG\Schema(

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
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function put(Product $product, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $customer = $this->security->getUser();
        dump($customer);
        /** @var $product Product */
        $replacement = $this->serializer->deserialize($request->getContent(), Product::class, 'json');
        /*a passer dans un handler avec les parametre suivant : l'objett, la deserialisation, les champs a modifier*/
        $product->setLastname($replacement->getLastname());
        $product->setfirstname($replacement->getFirstname());
        // a supprimer quand j'aurais le retour du token
        $product->setCustomer($entityManager->getRepository(Customer::class)->findOneBy([]));

        $entityManager->flush();
        $data = $this->serializer->serialize($product, 'json', SerializationContext::create()->setGroups(array('listing')));


        return new JsonResponse(
            null,
            JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id}",name="api_Products_item_delete", methods={"DELETE"})
     * @param Product $product
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function delete(Product $product, EntityManagerInterface $entityManager): JsonResponse
    {
        $customer = $this->security->getUser();
        dump($customer);
        $entityManager->remove($product);
        $entityManager->flush();

        return new JsonResponse(
            null,
            JsonResponse::HTTP_NO_CONTENT);
    }
}