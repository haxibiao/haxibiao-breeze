<?php

namespace Haxibiao\Breeze\Services;

class MetaService
{
    public function render()
    {
        return "<?php \$config = (new \Haxibiao\Breeze\Services\ManifestService)->generate(); echo \$__env->make('laravelpwa::meta' , ['config' => \$config])->render(); ?>";

    }

}
