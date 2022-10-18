<?php

namespace App\Entity;

use App\Repository\FactRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FactRepository::class)
 */
class Fact
{
    /**
     * @ORM\Column(type="float")
     * @ORM\JoinColumn(name="securityId", nullable=false, referencedColumnName="id")
     */
    private $value;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="Attribute", inversedBy="stockpediaFacts")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", nullable=false)
     */
    private $attribute;

     /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="Security", inversedBy="stockpediaFacts")
     * @ORM\JoinColumn(name="security_id", referencedColumnName="id", nullable=false)
     */
    private $security;


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
