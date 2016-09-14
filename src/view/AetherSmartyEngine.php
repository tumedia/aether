<?php

use Illuminate\View\Engines\EngineInterface;

class AetherSmartyEngine implements EngineInterface
{
    /** @var \AetherServiceLocator */
    protected $sl;

    /**
     * Constructor.
     *
     * @param \AetherServiceLocator  $sl
     */
    public function __construct(AetherServiceLocator $sl)
    {
        $this->sl = $sl;
    }

    /**
     * Make a fresh Smarty instance and configure it.
     *
     * @return \Smarty
     */
    protected function makeSmarty()
    {
        $smarty = new Smarty;

        $smarty->error_reporting = E_ALL ^ E_NOTICE;

        // Let's tell Smarty about the paths we've set up.
        $smarty->setTemplateDir(array_merge(
            // Paths that don't have a namespace. Should only be the project's
            // own templates.
            $this->sl->view()->getFinder()->getPaths(),

            // Map any "hints" from the Blade View Factory to be compatible
            // with Smarty's `[key]file.tpl` syntax (where `key` is the hint.)
            array_map(function ($array) {
                // Note: The Smarty Engine does not support assigning multiple
                // paths to a single hint.
                return $array[0];
            }, $this->sl->view()->getFinder()->getHints())
        ));

        // Now we'll tell Smarty that there might just be plugins living in the
        // template directories.
        $smarty->setPluginsDir(array_merge(array_map(function ($path) {
            return $path.'plugins';
        }, $smarty->getTemplateDir()), [
            // Let's keep the core plugins around!
            SMARTY_SYSPLUGINS_DIR,
            SMARTY_PLUGINS_DIR,
        ]));

        // Tell Smarty to put compiled PHP files in the correct directory.
        $smarty->setCompileDir($this->sl->get('projectRoot').'.cache/views');

        return $smarty;
    }

    /**
     * {@inheritDoc}
     */
    public function get($path, array $data = [])
    {
        $smarty = $this->makeSmarty();

        // This will assign data from the View Factory to the Smarty instance.
        // Will also assign shared variables, etc.
        foreach ($data as $key => $value) {
            $smarty->assign($key, $value);
        }

        return $smarty->fetch($path);
    }
}
