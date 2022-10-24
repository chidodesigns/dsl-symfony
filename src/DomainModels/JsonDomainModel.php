<?php 
namespace App\DomainModels;

use App\DomainModels\DomainModel;

class JsonDomainModel extends DomainModel
{
    protected $security;
    protected $expression; 


    public function __construct(string $security, array $expression)
    {
        parent::__construct($security, $expression);

        $this->security = $security;
        $this->expression = $expression;
    }

}