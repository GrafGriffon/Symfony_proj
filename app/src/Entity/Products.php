<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\ProductsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductsRepository::class)]
class Products
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $article;

    #[ORM\Column(name: 'date_create', type: 'date', nullable: true)]
    private $dateCreate;

    #[ORM\Column(name: 'date_update', type: 'date', nullable: true)]
    private $dateUpdate;

    #[ORM\Column(type: 'integer')]
    #[Assert\Length(min: 2, max: 3)]
    private $statusCount;

    #[ORM\Column(type: 'integer')]
    private $currPrice;

    #[ORM\OneToMany(targetEntity: "PriceHist", mappedBy: "product")]
    private $priceHist;

    #[ORM\OneToMany(targetEntity: "CountHist", mappedBy: "product")]
    private $countHist;

    #[ORM\ManyToMany(targetEntity: "Supply", mappedBy: "products")]
    private $supply;

    #[ORM\ManyToOne(targetEntity: "Category", inversedBy: "product")]
    #[ORM\JoinColumn(name: "category", onDelete: 'CASCADE')]
    private $category;

    public function __construct()
    {
        $this->supply = new ArrayCollection();
    }

    /**
     * @return Collection|Supply[]
     */
    public function getSupply(): Collection
    {
        return $this->supply;
    }

    public function addSupply(Supply $sup): self
    {
        if (!$this->supply->contains($sup)) {
            $this->supply[] = $sup;
        }
        return $this;
    }

    public function removeSupply(Supply $sup): self
    {
        $this->supply->removeElement($sup);
        return $this;
    }

//    #[ORM\ManyToOne(targetEntity: "Category")]
//    #[ORM\JoinColumn(name: "parent", referencedColumnName: "id", nullable: true)]
//    private $parent;

    public function __toString()
    {
        return $this->id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getArticle(): int
    {
        return $this->article;
    }

    public function setArticle(int $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function getDateCreate(): \DateTimeInterface
    {
        return $this->dateCreate;
    }

    public function setDateCreate(\DateTimeInterface $date): self
    {
        $this->dateCreate = $date;

        return $this;
    }

    public function getDateUpdate(): \DateTimeInterface
    {
        return $this->dateUpdate;
    }

    public function setDateUpdate(\DateTimeInterface $date): self
    {
        $this->dateUpdate = $date;

        return $this;
    }

    public function getStatusCount(): ?int
    {
        return $this->statusCount;
    }

    public function setStatusCount(int $statusCount): self
    {
        $this->statusCount = $statusCount;

        return $this;
    }

    public function getCurrPrice(): ?int
    {
        return $this->currPrice;
    }

    public function setCurrPrice(int $currPrice): self
    {
        $this->currPrice = $currPrice;
        return $this;
    }

    public function getPriceHist(): PriceHist
    {
        return $this->priceHist;
    }

    public function setPriceHist(PriceHist $priceHist): self
    {
        $this->priceHist = $priceHist;
        return $this;
    }

    public function getCountHist(): CountHist
    {
        return $this->countHist;
    }

    public function setCountHist(CountHist $countHist): self
    {
        $this->countHist = $countHist;
        return $this;
    }

}