# Templating

[source](https://git.tumedia.no/tumedia/aether/tree/master/src/Templating) - [tests](https://git.tumedia.no/tumedia/aether/tree/master/tests/Templating)

---

Aether uses the [Smarty template engine](https://www.smarty.net/) to render templates.

This document will not cover the Syntax and magical workings of Smarty, so if you aren't up to speed on the basics of Smarty, please refer to the [Smarty documentation](https://www.smarty.net/docs/en/).

## Basic Templating

### Creating a template

Templates are stored in the `templates` directory and suffixed with the `.tpl` file extension.

Example file **templates/article.tpl**:

``` smarty
<h1>{$title|escape}</h1>
<p>{$intro|escape}</p>
```

> Note that Smarty is **not** configured to escape variables by default, so you must be careful to escape all variables that should be escaped.
> In a scenario where the variable should deliberately be output unescaped, it is recommended to use the ["nofilter"](https://www.smarty.net/docs/en/variable.escape.html.tpl) Smarty flag to make the intent clear, like so: `{$title nofilter}`.

### Rendering a template

``` php
<?php

// 1) Get a fresh \Aether\Templating\Template instance to work with
$template = resolve('template');

// 2) Assign some data which will be loaded into the template
$template->set('title', 'BREAKING: New Smarty version released');
$template->set('intro', 'The world is stunned after the Smarty team released a new version');

// 3) Render the "article.tpl" template
return $template->fetch('article.tpl');
```

### `template()` helper function

The `template()` function is a nice little helper function for rendering templates.

* `template( void ) : \Aether\Templating\Template` returns a new `Template` instance
* `template( string $name [, array $data ] ) : string` renders and returns the template

``` php
<?php

// The same as the example above...

return template('article.tpl', [
    'title' => 'Smoother template rendering',
    'intro' => 'Render templates much smoother with this one weird trick',
]);
```

Using the `template()` function is the recommended way of rendering templates.

## Compiled Templates

The Smarty engines compiles templates into regular PHP upon request. These generated files are stored in `templates/compiled`.

To clear the compiled templates, you may use the `templates:clear` command:

```
vendor/bin/aether templates:clear
```

## Loading Templates From Packages

Let's say you want to create a package that contains a calendar module.

todo: write more

``` php
<?php

namespace Acme\Calendar;

use Aether\Providers\Provider;

class CalendarProvider extends Provider
{
    public function boot()
    {
        $this->loadTemplatesFrom(dirname(__DIR__).'/templates', 'calendar');
    }
}
```
