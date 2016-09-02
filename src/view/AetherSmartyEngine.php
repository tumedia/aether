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
            $this->sl->view()->getFinder()->getPaths(),
            array_reduce($this->sl->view()->getFinder()->getHints(), 'array_merge', [])
        ));

        // Now we'll Smarty that there might just be plugins living in the
        // template directories.
        $smarty->setPluginsDir(array_merge(array_map(function ($path) {
            return $path.'plugins';
        }, $smarty->getTemplateDir()), [
            // Let's keep the Smarty core plugins around!
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

        foreach ($data as $key => $value) {
            $smarty->assign($key, $value);
        }

        return $smarty->fetch($path);
    }
}
