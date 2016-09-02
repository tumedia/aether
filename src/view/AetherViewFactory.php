<?php

use Illuminate\View\Factory;

/**
 * Gotta extend the Laravel View Factory in order to maintain the old API.
 *
 * Doing this allows for doing something like this:
 *
 * ```
 * $tpl = $this->sl->getTemplate(); // Returns \AetherViewFactory
 * $tpl->set('some-variable', 'some-data');
 * return $tpl->fetch('some-template.tpl');
 * ```
 *
 * Of course, this is what you should be doing from now on:
 *
 * ```
 * return $this->sl->view('some-template', [
 *     'some-variable' => 'some-data',
 * ]);
 *
 * // Or...
 *
 * $view = $this->sl->view('some-template');
 *
 * $view->with('some-variable', 'some-data');
 * $view->with('foo', 'bar');
 *
 * return $view;
 * ```
 */
class AetherViewFactory extends Factory
{
    /** @var array */
    private $_data = [];

    /**
     * Assign some data to the view.
     *
     * @param  string $key
     * @param  mixed $value
     * @return void
     * @depricated  Since right now.
     */
    public function set($key, $value)
    {
        $this->_data[$key] = $value;
    }

    /**
     * Render the template.
     *
     * @param  string  $view
     * @return string
     * @depricated  Since right now.
     */
    public function fetch($view)
    {
        // Laravel Blade doesn't play well with file extensions. We'll get rid
        // of any '.tpl' at the end of the view name.
        if (($pos = stripos($view, '.tpl')) !== false) {
            $view = substr($view, 0, $pos);
        }

        $content = $this->make($view, $this->_data);

        // Reset the data array so it doesn't get passed on to the next call.
        // Just in case.
        $this->_data = [];

        return $content;
    }
}
