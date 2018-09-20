<?php

namespace Aether\Console\Commands;

use Aether\Console\Command;
use Aether\Templating\Template;

class TemplatesClearCommand extends Command
{
    protected $signature = 'templates:clear';

    protected $description = 'Clear all compiled template files';

    public function handle(Template $template)
    {
        $template->clearCompiled();

        $this->info('Cleared templates');
    }
}
