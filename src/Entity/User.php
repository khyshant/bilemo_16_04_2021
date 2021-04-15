<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;
use Swagger\Annotations as SWG;
/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @Hateoas\Relation(
 *   "listing",
 *       href=@Hateoas\Route(
 *               "api_users_listing",
 *               parameters={},
 *               absolute=true
 *           )
 *       )
 *       @Hateoas\Relation(
 *           "self",
 *           href=@Hateoas\Route(
 *               "api_users_item",
 *               parameters={"id" = "expr(object.getId())"},
 *               absolute=true
 *       )
 *   )
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @SWG\Property(description="The unique identifier of the user.")
     * @ORM\Column(type="integer")
     * @Serializer\Groups({"listing"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups({"detail", "listing"})
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Groups({"listing"})
     */
    private $firstname;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
    * @Serializer\Exclude 
     */
    private $customer;

    /**
     * @ORM\ManyToMany(targetEntity=Product::class, inversedBy="users")
     * @Serializer\Groups({"detail"})
     */
    private $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        $this->products->removeElement($product);

        return $this;
    }
}
