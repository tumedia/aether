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

        $base = $this->sl->get('projectRoot').'templates';
        $templatePaths[] = $base;

        $pluginPaths = [
            SMARTY_SYSPLUGINS_DIR,
            SMARTY_PLUGINS_DIR,
        ];

        foreach (AetherViewInstaller::getPaths() as $path) {
            $templatePaths[] = $path.'/templates';
            $pluginPaths[] = $path.'/templates/plugins';
        }

        $smarty->error_reporting = E_ALL ^ E_NOTICE;
        $smarty->template_dir = $templatePaths;
        $smarty->plugins_dir = $pluginPaths;
        $smarty->compile_dir = AetherViewInstaller::getCachePath();

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
