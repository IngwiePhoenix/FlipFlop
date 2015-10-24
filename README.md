# FlipFlop - A tiny view engine.

This is FlipFlop. A super, super tiny view engine that is used by BIRD3 to display things in it's new Laravel core.

I made this since I _just_ wanted to run regular PHP templates without anything super fancy. So I just made this!

## Install
Your typical

    composer require ingwiephoenix/bird3-flipflop

will work just fine.

Next, you'll want to pop `config/app.php` and look for

    Illuminate\View\ViewServiceProvider::class,

... and replace it with:

    BIRD3\Extensions\FlipFlop\Providers\FlipFlopServiceProfider::class,

And done!

## Usage
```php
<?php
// While bootstrapping...

// By default: app_path("Resources/Views/Layouts")
// But you can always append another path.
View::addTemplatePath(app_path("Resources/theme"));

// Change the global view path:
View::addViewPath(app_path("Resources/MyBetterViewFolder"));

// Or add modules and such as "namespace". Note, the two are idendical.
View::addViewPath("Derp", app_path("Derp/Views"));
View::addNamespace("Herp", app_path("Herp/Views"));

// When sending back a response...

Route::get("/", function(){
    return View::make("");
});

Route::get("/bad-guy", function(){
    $view = View::load("Herp::showcase");
    return $view(["foo"=>42]);
});
```

The `View::load()` and `View::make()` methods accept these parameters:

    ...FlipFlop\Manager::load($name, $args = [], $layout = null, $class = null)
    ...FlipFlop\Manager::make($name, $args = [], $layout = null, $class = null)

- `$name`: A name to a view. Use a double-colon to indicate a namespace. The path you add is relative to the view folder(s).
- `$args`: Pass arguments to the view or store them in the view.
- `$layout`: Change the normal layout that should be used.
- `$class`: Use this class instead of the normal "Engine" class. You are not restricted.

There are the other `add...()` convenience methods as well. Then, there are two resolve functions. Feel free to use them to pick up a view you have lost.
