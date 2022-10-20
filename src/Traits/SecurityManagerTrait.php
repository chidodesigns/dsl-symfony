<?php
namespace App\Traits;

use App\DoctrineModels\SecurityManager;

trait SecurityManagerTrait
{
    /** @var SecurityManager $securityManager */
    private $securityManager;
    
    /**
     * @param SecurityManager $securityManager
     * @Required
     */
    public function setSecuirtyManager(SecurityManager $securityManager)
    {

        $this->securityManager = $securityManager;
    }

}