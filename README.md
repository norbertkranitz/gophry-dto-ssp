# gophry-dto-ssp
Gophry DTO Service pProvider helps you to integrate gophry-dto ([learn more](https://github.com/norbertkranitz/gophry-dto)) to your Silex application.

This Silex service provider lets you to work with validated request object in your Silex action ([learn more](http://silex.sensiolabs.org)).

The provider merges the request body and the request query parameters, and converts the combination of them to a predefined ```\Gophry\DTO\RequestDTO``` based object.

Of course, you can define some validation rules - with the built in ```ValidationServiceProvider``` ([learn more](http://silex.sensiolabs.org/doc/providers/validator.html)) -  on the DTO object, so when the controller action is triggered you will have a validated request object, because request validation is done first.
If your request data was invalid then the provider will throw a ```\Gophry\Provider\DTO\InvalidRequestException```, so the server will response with status code ```422 Unprocessable Entity```, because this exception is th extension of ```\Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException```.

If you would like to handle those invalid requests on an other way, then you can configure the provider easily (please see below). The only thing you have to do is to change the exception class value.

## Installation

You can install the package with composer ([learn more](https://getcomposer.org/doc/01-basic-usage.md)).

```
composer require norbertkranitz/gophry-dto-spp "dev-master"
```

## Configure

```php
$app['gophry.invalid.request.exception.class'] = \My\Exception\Class;
```

> The first parameter of the exception is the message, the second one is the list of validation error.

## Register

You can simply register this service provider as a common Silex service provider ([learn more](http://silex.sensiolabs.org/doc/master/providers.html))

```php
$app->register(new DTOServiceProvider());
```

## Simple use

Assuming you have the login action on the ```POST /login``` endpoint, also the client sends a JSON request body.

```JSON
{
    "email": "test@user.app",
    "password": "password"
}
```

Create the LogiRequestDTO class:
```php
use \Symfony\Component\Validator\Mapping\ClassMetadata;
use \Symfony\Component\Validator\Constraints as Assert;

class LoginRequestDTO extends \Gophry\DTO\RequestDTO {

    protected $email;
    
    protected $password;
        
    public function getEmail() {
        return $this->email;
    }

    public function getPassword() {
        return $this->password;
    }

    static public function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('email', new Assert\NotBlank());
        $metadata->addPropertyConstraint('email', new Assert\Email());
        $metadata->addPropertyConstraint('password', new Assert\NotBlank());
        $metadata->addPropertyConstraint('password', new Assert\Length(['min' => 5]));
    }

}
```

Register the endpoint
```php
$app->post('/login', function(LoginRequestDTO $dto) {
    //do something with the valid request data
});
```

> See the type hinting in the action parameters. The extended controller resolver gets the referred type by a reflection method, so it can provide the parameter instance

> Don't forget to register the ```ValidatorServiceProvider``` if you would like to validat your request written above.

## Advanced use

Assuming the client sends a multilevel JSON request body to create a new user.

```JSON
{
    "email": "test@user.app",
    "password": "password",
    "name": {
        "first_name": "First",
        "last_name": "Last",
    }
}
```

Create the request NameRequestDTO class:
```php
use \Symfony\Component\Validator\Mapping\ClassMetadata;
use \Symfony\Component\Validator\Constraints as Assert;

class NameRequestDTO extends \Gophry\DTO\RequestDTO {

    protected $first_name;
    
    protected $last_name;
        
    public function getFirstName() {
        return $this->first_name;
    }

    public function getLastName() {
        return $this->last_name;
    }

    static public function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('first_name', new Assert\NotBlank());
        $metadata->addPropertyConstraint('last_name', new Assert\NotBlank());
    }

}
```

Create the request RegisterRequestDTO class:
```php
use \Symfony\Component\Validator\Mapping\ClassMetadata;
use \Symfony\Component\Validator\Constraints as Assert;

class RegisterRequestDTO extends \Gophry\DTO\RequestDTO {

    protected $email;
    
    protected $password;

    protected $name;

    //Important!
    public function __construct() {
        $this->name = new NameRequestDTO();
    }
        
    public function getEmail() {
        return $this->email;
    }

    public function getPassword() {
        return $this->password;
    }

    static public function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('email', new Assert\NotBlank());
        $metadata->addPropertyConstraint('email', new Assert\Email());
        $metadata->addPropertyConstraint('password', new Assert\NotBlank());
        $metadata->addPropertyConstraint('password', new Assert\Length(['min' => 5]));
        $metadata->addPropertyConstraint('name', new Assert\Valid());
    }

}
```

## Work with URI attributes

URI attributes are passed first. If your endpoint defines URI attribute(s) and uses the request body as well, just pass them to the action.

```php
$app->post('/login/{attribute}', function($attribute, LoginRequestDTO $dto) {
    //do something with the attribute and the valid request data
});
```

> In case of a sub DTO the ```\Gophry\DTO\RequestDTO``` requires to have it as an empty object, that can be inited in the constructor

> It's a good practice to use the ```Request``` phrase in case of each request DTO class, because Gophry can work with Response DTOs too!

> This provider is optimezed for JSON communication, also tested only with Silex!

