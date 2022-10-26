# Symfony Stockpedia DSL

A Symfony 4.4 DSL: 
The goal of the project is to build a small interpreter for a JSON based DSL that performs some simple analytics on a data set. This should be exposed as a Symfony-based API that accepts the queries.

## Running the App

- `composer install`
- Start Symfony Server: `symfony console server:start`
-  Using POSTMAN, We browser make a call to this `/facts/create` to build the DB. This will create the DB in the format given in the task.
- The you can begin making queries to `facts/dsl`

## Query Example 

{
    "security": "ABC",
    "expression": {
        "fn": "+",
        "a": {
            "fn": "-",
            "a": "price",
            "b": 20
        },
        "b": "sales"
    }
}

## Test Suite

- Set up database URL in `env.test` 
- USE FIXTURES CLASS TO LOAD DUMMY DATA: `php bin/console --env=test doctrine:fixtures:load`
- Run Test: `php bin/phpunit`
- Bug In Test: You can expect a bug to pop up when the test suite runs - was having issues with the ` static::createClient` making a request to the DSL from the test suite. Usually do not have issues with this - any debug tips welcomed. 


