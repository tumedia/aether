<?php

namespace Aether\Console\Tinker;

use Symfony\Component\VarDumper\Caster\Caster;

class TinkerCasters
{
    public static function castCollection($collection)
    {
        return [
            Caster::PREFIX_VIRTUAL.'all' => $collection->all(),
        ];
    }
}
