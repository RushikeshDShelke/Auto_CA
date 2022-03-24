<?php

namespace Dotzot\Grid\Controller\Adminhtml\Secondgrid;

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
            $this->_redirect('grid/secondgrid/addcol');
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
                   $tableName = $resource->getTableName('wk_secondgrid_records'); 
                   $sql = "Delete FROM " . $tableName."";
                   $connection->query($sql);
                  
                   while($row = fgetcsv($file_data))  
                   {  
                        $docket_no          =       $row[1];  

                        if((substr($row[1],0,2) == 'I3') || (substr($row[1],0,2) == 'i3')){
                            $type = 'C';
                        }
                        else
                        {
                            $type = 'P';
                        }
        
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
                        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                        $connection = $resource->getConnection();
                        $tableName = $resource->getTableName('wk_secondgrid_records'); 
                       
                        $sql = "Insert Into " . $tableName . " (docket_no, payment_method) Values ('$docket_no','$type')";
                        $connection->query($sql);
                   }  
                        $sql = "Select COUNT(*) as total FROM " . $tableName;
                        $result = $connection->fetchAll($sql);

              }
              
              $this->messageManager->addSuccess(__('Docket data has been successfully saved.'));
              $this->_redirect('grid/secondgrid/index');
        } 
         
        
         catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        
         } 
         else{
             $this->messageManager->addError(__('Please Upload Valid File.'));
             $this->_redirect('grid/secondgrid/addcol');
		}
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Dotzot_Grid::save');
    }
}
