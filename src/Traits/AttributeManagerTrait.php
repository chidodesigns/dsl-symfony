<?php
namespace App\Traits;

use App\DoctrineModels\AttributeManager;

trait AttributeManagerTrait
{
    /** @var AttributeManager $attributeManager */
    private $attributeManager;
    
    /**
     * @param AttributeManager $attributeManager
     * @Required
     */
    public function setAttributeManager(AttributeManager $attributeManager)
    {

        $this->attributeManager = $attributeManager;
    }

}