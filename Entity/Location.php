<?php

namespace Survos\LocationBundle\Entity;

use Stringable;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="Survos\LocationBundle\Repository\LocationRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="location_name_idx", columns={"name"}),
 *     @ORM\Index(name="location_lvl_idex", columns={"lvl"})
 * })
 * @Gedmo\Tree(type="nested")
 * @ApiResource()
 */
class Location implements Stringable
{

    public function __construct($code=null, $name=null, ?int $lvl=null)
    {
        $this->code = $code;
        $this->name = $name;
        $this->lvl = $lvl;

    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=180)
     * @Assert\NotBlank()
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=180)
     * @Assert\NotBlank()
     */
    private string $code;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    private int $lvl;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     * @var int|mixed|null
     */
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
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     * @var int|mixed|null
     */
    private $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\ManyToOne(targetEntity="Location", cascade={"persist"}, fetch="EAGER")
     * @ORM\JoinColumn(name="tree_root", referencedColumnName="id", onDelete="CASCADE")
     * @var \Survos\LocationBundle\Entity\Location|mixed|null
     */
    private $root;


    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Location", inversedBy="children", cascade={"persist"}, fetch="LAZY")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?\Survos\LocationBundle\Entity\Location $parent = null;


    /**
     * @ORM\OneToMany(targetEntity="Location", mappedBy="parent", cascade={"persist", "remove"}, fetch="LAZY")
     * @var \Survos\LocationBundle\Entity\Location[]|Collection|mixed|null
     */
    private ?Collection $children = null;

    /**
     * @ORM\Column(type="string", length=2, nullable=true)
     */
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
