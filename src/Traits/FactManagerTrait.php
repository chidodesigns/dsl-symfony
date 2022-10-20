<?php
namespace App\Traits;

use App\DoctrineModels\FactManager;

trait FactManagerTrait
{
    /** @var FactManager $factManager */
    private $factManager;
    
    /**
     *
     * @param FactManager $factManager
     * @Required
     */
    public function setFactManager(FactManager $factManager)
    {

        $this->factManager = $factManager;
    }

}