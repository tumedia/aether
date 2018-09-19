# Templating

[💾 Source](https://git.tumedia.no/tumedia/aether/tree/master/src/Templating) - [✅ Tests](https://git.tumedia.no/tumedia/aether/tree/master/tests/Templating)

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

Using the `template()` function is the recommended way to render templates.

## Compiled Templates

The Smarty engines compiles templates into regular PHP upon request. These generated files are stored in `templates/compiled`.

To clear the compiled templates, you may use the `templates:clear` command:

```
vendor/bin/aether templates:clear
```

## Template Namespaces

Let's say you want to create a package that contains a calendar module.

In a service provider, call the `loadTemplatesFrom()` method, like so:

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

Smarty will now load templates from the calendar package's `templates/` directory when the `calendar` namespace is used.

### Rendering a Namespaced Template

The syntax used to render a namespaced template is as follows:

```
namespace:path/to/template.tpl
```

For example:

``` php
return template('calendar:upcoming-events.tpl');
```

### Overriding a Namespaced Template

Let's say the calendar package contains a view that renders a single calendar event, and you want to override it locally, you may do so by creating a local override which will be used when the template is rendered.

First you will need to create a special folder called `vendor` within your templates directory. Inside the vendor folder, create a folder with the same name as the template namespace you wish to override.

For example:

```
vim templates/vendor/calendar/event.tpl
```

Doing this will completely replace the original template. If, however, you wish to *extend* the original template, you must include a special `!`, like so:

```
<!-- calendar:event.tpl -->
{extends "calendar:!event.tpl"}

{block name="foo"} ... {/block}
```

> The `!` is used to signal to Smarty that the original template should be used. Forget this and you'll be in for an infinite loop.

The same rule applies if you want to *include* the original template:

```
<!-- calendar:event.tpl -->
<div class="my-cool-wrapper">
    {include "calendar:!event.tpl"}
</div>
```
