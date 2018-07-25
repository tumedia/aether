<?php

namespace Tests\Fixtures\Sections;

use Aether\Response\Text;
use Aether\Sections\Section;

class GenericTestSection extends Section
{
    public function response()
    {
        http_response_code(200);

        return new Text($this->renderModules());
    }
}
