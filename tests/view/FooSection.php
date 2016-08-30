<?php

class FooSection extends AetherSection
{
    public function response()
    {
        return new AetherTextResponse($this->renderModules(), 'text/html');
    }
}
