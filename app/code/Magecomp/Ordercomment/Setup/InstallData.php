<?php
namespace Magecomp\Ordercomment\Setup;

use Magecomp\Ordercomment\Helper\Data\Ordercomment;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Quote\Setup\QuoteSetupFactory;

class InstallData implements InstallDataInterface
{
    protected $salesSetupFactory;
    protected $quoteSetupFactory;

    public function __construct(SalesSetupFactory $salesSetupFactory, QuoteSetupFactory $quoteSetupFactory) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $quoteInstaller = $this->quoteSetupFactory->create(['resourceName' => 'quote_setup', 'setup' => $setup]);
        $salesInstaller = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);

        $quoteInstaller->addAttribute(
            'quote',
            Ordercomment::COMMENT_FIELD_NAME,
            ['type' => Table::TYPE_TEXT, 'length' => '64k', 'nullable' => true]
        );

        $salesInstaller->addAttribute(
            'order',
            Ordercomment::COMMENT_FIELD_NAME,
            ['type' => Table::TYPE_TEXT, 'length' => '64k', 'nullable' => true, 'grid' => true]
        );
        $setup->endSetup();
    }
}
