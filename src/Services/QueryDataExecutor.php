<?php
namespace App\Services;

use App\DoctrineModels\AttributeManager;
use App\DoctrineModels\FactManager;
use App\DoctrineModels\SecurityManager;
use Doctrine\ORM\EntityManagerInterface;

class QueryDataExecutor
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var ExpressionLanguageService $expressionLanguageService */
    private $expressionLanguageService;

    public function __construct(EntityManagerInterface $entityManagerInterface)
    {
        $this->entityManager = $entityManagerInterface;
        $this->expressionLanguageService = new ExpressionLanguageService();
    }
  
    public function getSecurityFromDB ($securitySymbol)
    {
        $securityManager = new SecurityManager;
        $dbSecurity = $securityManager->findSecuritySymbol($this->entityManager, $securitySymbol);
        return $dbSecurity;

    }

    public function getAttributeFromDB ($attributeName)
    {
        $attributeManager = new AttributeManager;
        $dbAttribute = $attributeManager->findAttributeName($this->entityManager, $attributeName);
        return $dbAttribute;
    }

    public function getFactValueFromDB ($attrId, $secSymbolId)
    {
        $factManager = new FactManager;
        $dbFactValue = $factManager->selectFact($this->entityManager, $attrId, $secSymbolId);
        return $dbFactValue;
    }

    public function executeExpression($expression)
    {
        $evaluatedExpression = $this->expressionLanguageService->evaluateExpression($expression);
        return $evaluatedExpression;

    }
}