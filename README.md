
CakePHP 3.0 plugin to update e.g. `created_by` and `modified_by` fields.

## Installation

Add the following lines to your application's `composer.json`:

```
    "require": {
        "hmic/cakephp-blame": "dev-master"
    }
```

followed by the command:

`composer update`

Or run the following command directly without changing your `composer.json`:

`composer require hmic/cakephp-blame:dev-master`

OR:
Create a `plugins/Blame` dir under your app's dir and checkout this repo
and make sure `['autoload' => true]` is set when loading the plugin like shown below.

## Usage

In your app's `config/bootstrap.php` add: `Plugin::load('Blame', ['autoload' => true])`;

## Configuration

Add the following line to your AppController:

```
    use \Blame\Controller\BlameTrait;
```

Attach the behavior in the models you like to use.
Please note that these need to have the following 2 fields in the database table for it to work:
`created_by int(11) NULL` and `modified_by int(11) NULL`

```
    public function initialize(array $config) {
        $this->addBehavior('Blame.Blame');
    }
```

## Routing
Has been fixed in the core in the meantime - just works right now!
<del>
This is work in progress, but you can have routing working, for use with baked views,
(works only for the default configuration for now) by adding this to your app's
`config/routes.php` directly defore the `$routes->fallbacks();` and commenting that out,
like this:

```
 	$routes->connect('/:controller', ['action' => 'index'], ['routeClass' => 'Blame.BlameRoute']);
 	$routes->connect('/:controller/:action/*', [], ['routeClass' => 'Blame.BlameRoute']);
//	$routes->fallbacks();
```
</del>
## Bake
If you want to bake your stuff you need to bake the model first, then add the behavior as shown
above and afterwards bake the controller and views. It will take care of the associations
automatically. For the routing of the baked views to work for the assocations too, see above.
