# Events

Aether supports events using the [illuminate/events package from Laravel](https://laravel.com/docs/5.5/events).

**To learn more about how to generate events, event listeners etc., refer to the [Laravel documentation](https://laravel.com/docs/5.5/events).**

> Note that Aether does not currently support [event queueing](https://laravel.com/docs/5.5/events#queued-event-listeners).

## Defining listeners in the config

In **config/events.php**, add a `"listen"` array that contains an array of listeners.

- The key corresponds to the event you want to listen for. (see [defining events](https://laravel.com/docs/5.5/events#defining-events))
- The value is an array of any number of event listeners. (see [defining listeners](https://laravel.com/docs/5.5/events#defining-listeners))

```
<?php

return [
    'listen' => [
        'Some\Package\Events\UserWasRegistered' => [
            'App\Listeners\SendWelcomeMail',
        ],
    ],
];
```

### Event Subscribers

Laravel has a concept of ["Event Subscribers" (click to learn more.)](https://laravel.com/docs/5.5/events#event-subscribers)

In order to register a subscriber, add a `"subscribe"` array to the **config/events.php** file:

```
<?php

return [
    'subscribe' => [
        'App\Path\To\SomeEventSubscriber',
    ],
];
```

## Dispatching an Event

You may use the `event()` helper function to fire an event:

```
<?php

event(new UserWasRegistered($user));
```

## Resolving the event dispatcher instance

```
<?php

$events = resolve('events');

// Returns `Illuminate\Events\Dispatcher`
```

Manually resolving the event dispatcher can be useful in package code where you want to listen to an event, as the configuration (below) is not necessarily available. For instance:

```
$events = resolve('events');

$events->listen('MyPackage\Events\SomethingHappened', 'MyPackage\Listeners\DoSomething');
```
