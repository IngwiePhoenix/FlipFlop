# FlipFlop - A tiny view engine.

This is FlipFlop. A super, super tiny view engine that is used by BIRD3 to display things in it's new Laravel core.

I made this since I _just_ wanted to run regular PHP templates without anything super fancy. So I just made this!

## Features at a glance
- A view is rendered into a layout by default.
- Partial views via `__partial__` variable, or by using the FlipFlop facade.
- Views have contexts, so they can use `$this`.
- It just overrides your current `PhpEngine`, meaning that we are 100% API compatible.

## Install
Your typical

    composer require ingwiephoenix/bird3-flipflop

will work just fine.

Next, you'll want to pop `config/app.php` and look for

    Illuminate\View\ViewServiceProvider::class,

... and add this underneath:

    BIRD3\Extensions\FlipFlop\Providers\FlipFlopServiceProfider::class,

... and within the `alias` section, you'd add:

    "FlipFlop" => BIRD3\Extensions\FlipFlop\Facades\FlipFlop::class,

.. and it's done!

## Usage
```php
<?php
// While bootstrapping...

// By default: app_path("Resources/Views/Layouts")
// But you can always append another path.
View::addLocation(app_path("Resources/theme"));

// Or add modules and such as "namespace". Note, the two are idendical.
View::addNamespace("Derp", app_path("Derp/Views"));

// When sending back a response...

Route::get("/", function(){
    return View::make("Derp::index");
});

Route::get("/bad-guy", function(){
    $view = View::make("Herp::showcase");
    return $view->with(["foo"=>42]);
});

// Use a custom context.
Route::get("/customer", function(){
    // Using traditional View API:
    $view = View::make("Customer::main");
    $view->getEngine()->setContext(Auth::user());

    // Via facade
    $view = FlipFlop::loadWithContext("Customer::main", Auth::user());

    return $view;
});
```

## Layouts
You usually want to define your layout just like any other template. Say you put it into `app/Resources/Views/Layouts/main.php`, then you would be able to go right ahead, since this is the default that FlipFlop will look for. To change that:

    FlipFlop::setDefaultLayout($viewName)

Just like views, layouts are resolved the same way.

You get the content of the view via the `$contents` variable - but you are free to use the features provided by the `View` facade as well. If you didn't know, check out what [it can actually do](http://laravel.com/api/5.1/Illuminate/View/View.html).
