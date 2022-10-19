<?php
namespace App\Traits;

use App\Repository\FactRepository;

trait FactRepositoryTrait
{

    /** @var FactRepository $factRepository */
    protected $factRepository;

    /**
     *
     * @param FactRepository $factRepository
     * @Required
     * @return void
     */    
    public function setFactRepository(FactRepository $factRepository)
    {
        $this->factRepository = $factRepository;
    }

}