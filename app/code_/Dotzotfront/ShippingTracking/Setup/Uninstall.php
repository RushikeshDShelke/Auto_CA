<?php
/**
 * Dotzot Shipping
 */

namespace Dotzotfront\ShippingTracking\Setup;

class Uninstall extends \Dotzotfront\Base\Setup\AbstractUninstall
{
    protected $_configSectionId = 'prshippingtracking';
    protected $_pathes = ['/app/code/Dotzotfront/ShippingTracking'];
}