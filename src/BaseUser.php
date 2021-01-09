<?php

namespace Haxibiao\Base;

use Haxibiao\Content\Traits\UseContent;
use Haxibiao\Media\Traits\UseMedia;

class BaseUser extends User
{

    use UseMedia;
    use UseContent;

}
