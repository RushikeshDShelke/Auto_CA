<?php

namespace Dotzot\Grid\Controller\Adminhtml\Grid;

class Save extends \Magento\Backend\App\Action
{
    var $gridFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Dotzot\Grid\Model\GridFactory $gridFactory
    ) {
        parent::__construct($context);
        $this->gridFactory = $gridFactory;
    }

   public function execute()
    {
        $post_data=$this->getRequest()->getPostValue();
        if (!$post_data) {
            $this->_redirect('grid/grid/addrow');
            $this->messageManager->addError(__('Please Upload Valid File.'));
            return;
        }
        
        if(!empty($_FILES["title"]["name"]))  
         {  
             try {
              $allowed_ext = array("csv");  
              $parts = explode('.', $_FILES["title"]["name"]);
              $extension = end($parts);
              if(in_array($extension, $allowed_ext))  
              {  
                   $file_data = fopen($_FILES["title"]["tmp_name"], 'r');  
                   fgetcsv($file_data); 
                                           
                   $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
                   $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                   $connection = $resource->getConnection();
                   $tableName = $resource->getTableName('wk_grid_records'); 
                   $sql = "Delete FROM " . $tableName."";
                   $connection->query($sql);

                   while($row = fgetcsv($file_data))  
                   {  
                        $sr = $row[0];  
                        $product = $row[1]; 
                        $pincode = $row[2];
                        $city = $row[3];
                        $state = $row[4];
                        $region = $row[5];
                        $prepaid = $row[6];
                        $cod = $row[7];
                        $reversepickup = $row[8];
                        $pickup = $row[9];
                        $service = $row[10];
                        $sql = "Insert Into " . $tableName . " (sr, product, pincode, city,state,region,prepaid,cod,reversepickup,pickup,service) Values ('$sr','$product','$pincode','$city','$state','$region','$prepaid','$cod','$reversepickup','$pickup','$service')";
                        $connection->query($sql);
                   }  
                        $sql = "Select COUNT(*) as total FROM " . $tableName;
                        $result = $connection->fetchAll($sql);

              }
              
              $this->messageManager->addSuccess(__('Pincode data has been successfully saved.'));
              $this->_redirect('grid/grid/index');
        } 
         
        
         catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        
         } 
         else{
             $this->messageManager->addError(__('Please Upload Valid File.'));
             $this->_redirect('grid/grid/addrow');
		}
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Dotzot_Grid::save');
    }
}
