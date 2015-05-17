# test-tools
Unit / Behaviour test tools

## Standalone Symfony Bundle Configuration testing

Create your AppKernel:

```php
use Webit\Tests\Behaviour\Bundle\Kernel as BaseKernel;

class AppKernel extends BaseKernel
{
    public function registerBundles()
    {
        return array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new My\BrandNewBundle();
        ); // array of your Bundles
    }
}
```

Create your FeatureContext and register the Kernel:

```php
use Webit\Tests\Behaviour\Bundle\BundleConfigurationContext;

class FeatureContext extends BundleConfigurationContext
{
    public function __construct()
    {
        parent::__construct(new AppKernel());
    }
}
```

Scenario example:

```
Feature: MyBrandNewBundle configuration feature
  In order to set up MyBrandNew library with Symfony Application
  As a developer
  I need Bundle Configuration / Extension
  
  Background:
    Given the configuration contains:
    """
    framework:
        secret: "my-secret-hash"
        
    my_brand_new: ~
    """
  
  Scenario: Basic configuration
    When I bootstrap the application
    Then there should be following services defined:
    """
    my_service_one, my_service_two, my_service_three
    """
    And there should be following aliases defined:
    | service                    | alias              |
    | my_service.default_service | my_service.service |
    And all given services should be reachable
```

Create as many scenarios as you need (for different configuration options).
Feel free to add any other checks (steps) into your Context.
