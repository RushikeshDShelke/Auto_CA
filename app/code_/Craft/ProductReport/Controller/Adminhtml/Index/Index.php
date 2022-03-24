<?php
namespace Craft\ProductReport\Controller\Adminhtml\Index;

use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;

class Index extends \Magento\Backend\App\Action
{
        private $stockRegistry;
        protected $resultPageFactory = false;      
        
        public function __construct(    
                \Magento\Backend\App\Action\Context $context,           
                \Magento\Catalog\Helper\Data $taxHelper,                        
                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
                StockRegistryInterface $stockRegistry,        
                array $data = []

         ) {
                $this->taxHelper = $taxHelper;
                $this->_productCollectionFactory = $productCollectionFactory;    
                $this->stockRegistry = $stockRegistry;
                parent::__construct($context);
         } 
         public function execute()
         {
            $collection = $this->_productCollectionFactory->create();
            $collection->addAttributeToSelect('*');
            // $collection->setPageSize(20); 
            $formatted_array = [];
            $i = 0;
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $StockState = $objectManager->get('\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');          
            foreach($collection as $product) {

                               
             
                if($product->getTypeId() == 'simple'){
                    $qty = $StockState->execute($product->getSku()); 
                    if(!empty($qty)) {                    
                        $salable_qty = $qty[0]['qty'];
                        if ($salable_qty > 0 ) {
                            $price = $this->taxHelper->getTaxPrice($product, $product->getFinalPrice(), true);                
                            $formatted_array[$i]['product_name'] = $product->getName();
                            $formatted_array[$i]['sku'] = $product->getSku();
                            $formatted_array[$i]['price'] = $price;
                            $formatted_array[$i]['product_url'] = $product->getProductUrl();
                        }                       
                    }                                   
                }
                $i++;
            }         
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=product_report.csv');
            $output = fopen('php://output', 'w');
            fputcsv($output, array('Product Name' , 'Sku' , 'Price' , 'Product Url'));
            if (count($formatted_array) > 0) {
                foreach ($formatted_array as $row) {
                    fputcsv($output, $row);
                }
            }
         }
         
}