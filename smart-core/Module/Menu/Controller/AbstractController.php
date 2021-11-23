<?php

namespace SmartCore\Module\Menu\Controller;

use SmartCore\Bundle\CMSBundle\Module\CacheTrait;
use SmartCore\Bundle\CMSBundle\Module\NodeTrait;
use Smart\CoreBundle\Controller\Controller;

abstract class AbstractController extends Controller
{
    use CacheTrait;
    use NodeTrait;

    protected $css_class     = null;
    protected $current_class = 'active';
    protected $depth         = 0;
    protected $menu_id       = null;
    protected $selected_inheritance = false;
}
