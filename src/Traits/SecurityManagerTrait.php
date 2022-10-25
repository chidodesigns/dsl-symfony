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
    public function setSecurityManager(SecurityManager $securityManager)
    {

        $this->securityManager = $securityManager;
    }

    public function getSecurity($symbol)
    {
        $securitySymbol = $this->securityManager->findSecuritySymbol($symbol);
        return  $securitySymbol;
    }

}