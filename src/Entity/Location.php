<?php

namespace Survos\LocationBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Stringable;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Survos\Grid\Api\Filter\MultiFieldSearchFilter;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: 'Survos\LocationBundle\Repository\LocationRepository')]
#[ORM\Table(indexes: [new ORM\Index(name: 'location_name_idx', columns: ['name']), new ORM\Index(name: 'location_lvl_idex', columns: ['lvl'])])]
#[Gedmo\Tree(type: 'nested')]
#[UniqueEntity('code')]
#  [ORM\UniqueConstraint(name: 'location_code', columns: ['code'])]
#[ApiResource(
    normalizationContext: ['skip_null_values' => false, 'groups' => ['rp', 'location.read', 'location.tree']],
)]
#[ApiFilter(OrderFilter::class, properties: ['code', 'name'], arguments: ['orderParameterName' => 'order'])]
#[ApiFilter(MultiFieldSearchFilter::class, properties: ["code", 'name'], arguments: ["searchParameterName"=>"search"])]
#[ApiFilter(SearchFilter::class, properties: ['name'=>'partial','code' => 'exact','lvl' => 'exact'])]

class Location implements Stringable
{
    public function __construct($code=null, $name=null, ?int $lvl=null)
    {
        $this->code = $code;
        $this->name = $name;
        $this->lvl = $lvl;
    }
    public static function build(string $code, string $name, ?int $lvl): self
    {
        $location = (new Location($code, $name, $lvl))
//            ->setCode($code)
//            ->setName($name)
//            ->setLvl($lvl)
        ;

        return $location;
    }
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['location.read'])]
    private ?int $id = null;
    #[ORM\Column(type: 'string', length: 180)]
    #[Assert\NotBlank]
    #[Groups(['location.read'])]
    private string $name;
    #[ORM\Column(type: 'string', length: 180)]
    #[Assert\NotBlank]
    #[Groups(['location.read'])]
    private string $code;

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Groups(['location.read'])]
    private int $lvl;
    /**
     * @var int|mixed|null
     */
    #[ORM\Column(name: 'lft', type: 'integer')]
    #[Gedmo\TreeLeft]
    private $lft;
    /**
     * @return mixed|null
     */
    public function getLft()
    {
        return $this->lft;
    }
    /**
     * @param mixed $lft
     */
    public function setLft($lft): static
    {
        $this->lft = $lft;
        return $this;
    }
    /**
     * @return mixed|null
     */
    public function getRgt()
    {
        return $this->rgt;
    }
    /**
     * @param mixed $rgt
     */
    public function setRgt($rgt): static
    {
        $this->rgt = $rgt;
        return $this;
    }
    /**
     * @return mixed|null
     */
    public function getRoot()
    {
        return $this->root;
    }
    /**
     * @param mixed $root
     */
    public function setRoot($root): static
    {
        $this->root = $root;
        return $this;
    }
    /**
     * @return mixed
     */
    public function getParent(): ?Location
    {
        return $this->parent;
    }
    public function setParent(?Location $parent): self
    {
        $this->parent = $parent;
        return $this;
    }
    /**
     * @return mixed|null
     */
    public function getChildren(): ?Collection
    {
        return $this->children;
    }
    /**
     * @param mixed $children
     */
    public function setChildren($children): static
    {
        $this->children = $children;
        return $this;
    }
    /**
     * @return mixed
     */
    public function getLvl(): ?int
    {
        return $this->lvl;
    }
    public function setLvl(?int $lvl): static
    {
        $this->lvl = $lvl;
        return $this;
    }
    /**
     * @var int|mixed|null
     */
    #[ORM\Column(name: 'rgt', type: 'integer')]
    #[Gedmo\TreeRight]
    private $rgt;
    /**
     * @var \Survos\LocationBundle\Entity\Location|mixed|null
     */
    #[ORM\ManyToOne(targetEntity: 'Location', cascade: ['persist'], fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'tree_root', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Gedmo\TreeRoot]
    private $root;
    #[ORM\ManyToOne(targetEntity: 'Location', inversedBy: 'children', cascade: ['persist'], fetch: 'LAZY')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Gedmo\TreeParent]
    private ?\Survos\LocationBundle\Entity\Location $parent = null;
    /**
     * @var \Survos\LocationBundle\Entity\Location[]|Collection|mixed|null
     */
    #[ORM\OneToMany(targetEntity: 'Location', mappedBy: 'parent', cascade: ['persist', 'remove'], fetch: 'LAZY')]
    private ?Collection $children = null;
    #[ORM\Column(type: 'string', length: 2, nullable: true)]
    private ?string $alpha2 = null;
    public function getId(): ?int
    {
        return $this->id;
    }
    public function getCode(): ?string
    {
        return $this->code;
    }
    public function setCode(string $code): self
    {
        $this->code = $code;

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
    public function getAlpha2(): ?string
    {
        return $this->alpha2;
    }
    public function setAlpha2(?string $alpha2): self
    {
        $this->alpha2 = $alpha2;

        return $this;
    }
    public function __toString(): string
    {
        return $this->getName();
    }
}
