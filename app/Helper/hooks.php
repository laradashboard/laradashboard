<?php

use TorMorten\Eventy\Facades\Events as Eventy;

/**
 * Add action to Eventy.
 *
 * This is a WordPress like add_action hook implementation.
 *
 * @see https://github.com/tormjens/eventy
 *
 * @param  mixed  $callback
 * @param  mixed  $priority
 * @param  mixed  $args
 * @return void
 */
function ld_add_action(string $hookName, $callback, $priority = 20, $args = 1)
{
    Eventy::addAction($hookName, $callback, $priority, $args);
}

/**
 * Do action in Eventy.
 *
 * This is a WordPress like do_action hook implementation.
 *
 * @see https://github.com/tormjens/eventy
 *
 * @param  string  $hookName
 * @param  mixed  ...$args
 * @return void
 */
function ld_do_action(string $hookName, ...$args): void
{
    if (count($args) === 0) {
        Eventy::action($hookName);
    } elseif (count($args) === 1) {
        Eventy::action($hookName, $args[0]);
    } else {
        // Handle multiple arguments by passing them as an array
        Eventy::action($hookName, $args);
    }
}

/**
 * Apply filters in Eventy.
 *
 * This is a WordPress like apply_filters hook implementation.
 *
 * @see https://github.com/tormjens/eventy
 *
 * @param  mixed  $value
 * @param  mixed  $args
 * @return mixed
 */
function ld_apply_filters(string $hookName, $value, $args = null)
{
    return Eventy::filter($hookName, $value, $args);
}

/**
 * Add filter to Eventy.
 *
 * This is a WordPress like add_filter hook implementation.
 *
 * @see https://github.com/tormjens/eventy
 *
 * @param  mixed  $callback
 * @param  mixed  $priority
 * @param  mixed  $args
 * @return mixed
 */
function ld_add_filter(string $hookName, $callback, $priority = 20, $args = 1)
{
    return Eventy::addFilter($hookName, $callback, $priority, $args);
}
