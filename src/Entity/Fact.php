<?php

namespace App\Entity;

use App\Repository\FactRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FactRepository::class)
 */
class Fact
{
    /**
     * @ORM\Column(type="float")
     * 
     */
    private $value;

      /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="Security", inversedBy="stockpediaFacts")
     * @ORM\JoinColumn(name="security_id", referencedColumnName="id", nullable=false)
     */
    private $security;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="Attribute", inversedBy="stockpediaFacts")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", nullable=false)
     */
    private $attribute;

   
 
    public function __construct()
    {
        $this->stockpediaFacts = new ArrayCollection();
    }


    public function getSecurity()
    {
        return $this->security;
    }

    public function setSecurity(Security $security):self
    {
        $this->security = $security;
        return $this;
    }

    public function getAttribute()
    {
        return $this->attribute;
    }

    public function setAttribute(Attribute $attribute):self
    {
        $this->attribute = $attribute;
        return $this;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    { 
        $this->value = $value;

        return $this;
    } 
}
