<?php
/**

 */

namespace Dotzotfront\Base\Setup;

/* Uninstall Base */
class Uninstall extends AbstractUninstall
{
    
    /**
     * @var array
     */
    protected $_tables = ['plumbase_product'];

    /**
     * @var array
     */
    protected $_pathes = ['/app/code/Dotzotfront/Base'];
}
