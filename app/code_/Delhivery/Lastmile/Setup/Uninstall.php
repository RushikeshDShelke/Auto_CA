<?php
/**
 * Delhivery_Lastmile extension
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category  Delhivery
 * @package   Delhivery_Lastmile
 * @copyright Copyright (c) 2018
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Delhivery\Lastmile\Setup;

class Uninstall implements \Magento\Framework\Setup\UninstallInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.Generic.CodeAnalysis.UnusedFunctionParameter)
     */
    public function uninstall(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        if ($setup->tableExists('delhivery_lastmile_awb')) {
            $setup->getConnection()->dropTable('delhivery_lastmile_awb');
        }
        if ($setup->tableExists('delhivery_lastmile_pincode')) {
            $setup->getConnection()->dropTable('delhivery_lastmile_pincode');
        }
        if ($setup->tableExists('delhivery_lastmile_location')) {
            $setup->getConnection()->dropTable('delhivery_lastmile_location');
        }
    }
}
