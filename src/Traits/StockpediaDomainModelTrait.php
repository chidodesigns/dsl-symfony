<?php
namespace App\Traits;

use App\DomainModels\StockpediaBasicModel;

trait StockpediaDomainModelTrait 
{

    /** @var StockpediaBasicModel $domainModel */
    private $domainModel;

    /**
     *
     * @param StockpediaBasicModel $domainModel
     * @Required
     */
    public function setStockpediaDomainModel(StockpediaBasicModel $domainModel)
    {
        $this->domainModel = $domainModel;
    }

}