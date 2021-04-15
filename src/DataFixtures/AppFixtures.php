<?php

namespace App\DataFixtures;

use App\Entity\Brand;
use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\BrandRepository;
use App\Repository\CustomerRepository;
use App\Repository\ProductRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{

    /**
     * @var UserPasswordEncoderInterface
     */
    private $userPasswordEncoder;

    /**
     * @var BrandRepository
     */
    private $brandRepository;
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     * @param BrandRepository $brandRepository
     * @param CustomerRepository $customerRepository
     * @param ProductRepository $productRepository
     */
    public function __construct(UserPasswordEncoderInterface $userPasswordEncoder, BrandRepository $brandRepository, CustomerRepository $customerRepository, ProductRepository $productRepository)
    {
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->brandRepository = $brandRepository;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
    }

    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);
        for($i = 1; $i <= 3; $i++){
            $customer = new Customer();
            $customer->setCustomerName(sprintf("customer_%d",$i));
            $customer->setFirstname(sprintf("firstname_%d",$i));
            $customer->setLastname(sprintf("lastname_%d",$i));
            $customer->setEmail(sprintf("email_%d@test.fr",$i));
            $customer->setPassword($this->userPasswordEncoder->encodePassword($customer, 'azerty123'));
            $manager->persist($customer);
            $manager->flush();
        }
        for($i = 1; $i <= 3; $i++){
            $brand = new Brand();
            $brand->setDescription(sprintf("description_%d",$i));
            $brand->setName(sprintf("name_%d",$i));
            $manager->persist($brand);
            $manager->flush();
        }
        for($i = 1; $i <= 15; $i++){
            $product = new Product();
            $product->setDescription(sprintf("description_%d",$i));
            $brand = $this->brandRepository->findOneBy(["id"=> rand(1,3)]);
            $product->setBrand($brand);
            $manager->persist($product);
            $manager->flush();
        }
        for($i = 1; $i <= 10; $i++){
            $user = new User();
            $customer = $this->customerRepository->findOneBy(["id"=> rand(1,3)]);
            $user->setCustomer($customer);
            $user->setLastname(sprintf("lastname_%d",$i));
            $user->setFirstname(sprintf("firstname_%d",$i));
            for($j = 1; $j < rand(3,8); $j++){
                $linked = [] ;
                $p = rand(1,15);
                if(!in_array($p,$linked)) {
                    $linked[] = $p;
                    $product= $this->productRepository->findOneBy(["id"=> $p]);
                    $user->addProduct($product);
                }
            }
            $manager->persist($user);
            $manager->flush();
        }

    }
}
