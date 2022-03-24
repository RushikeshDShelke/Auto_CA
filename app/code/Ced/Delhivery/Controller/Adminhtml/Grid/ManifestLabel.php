<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_Delhivery
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace  Ced\Delhivery\Controller\Adminhtml\Grid;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;

class ManifestLabel extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */

    /**
     * @var FileFactory
     */
    protected $_fileFactory;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        ForwardFactory $resultForwardFactory
    ) {    
        $this->_fileFactory = $fileFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|\Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {   
    	$waybills=$this->getRequest()->getParam('selected',false);
    	if(!$waybills){
    		$this->messageManager->addErrorMessage(__('There are no printable labels related to selected waybills.'));
			$this->_redirect('*/grid/awb');
    	}
    	$wayBills =[];
    	foreach ($waybills as $waybill) {
    	 	$awb = $this->_objectManager->create('Ced\Delhivery\Model\Awb')->load($waybill)->getData();
    	 	$wayBills[] = $awb['awb'];
    	}
		
		$flag = false;
		if (!empty($wayBills))
		{
			$labelperpage=1;
	  		$totalpages = sizeof($waybills)/$labelperpage;
	  		$pdf = new \Zend_Pdf ();
	  		$style = new \Zend_Pdf_Style ();
	  		for ($page_index = 0; $page_index<$totalpages; $page_index++)
	  		{
		  		$page = new \Zend_Pdf_Page(\Zend_Pdf_Page::SIZE_A4 );
		  		$pdf->pages[] = $page;
	  		}

	  		$pagecounter = -1;
	  		$i=0; $y=830;
			foreach ($wayBills as $_wayBills)
			{	
				$orderid ='';
				$awb = $this->_objectManager->create('Ced\Delhivery\Model\Awb')->getCollection()->addFieldToFilter('awb',trim($_wayBills))->getData();
				if(empty($awb))
				{
					continue;				
				}
				
				foreach($awb as $value)
				{
					if($value['state']!=1)
						continue;
					$orderid=$value['orderid'];
					$shipment_id = $value['shipment_id'];
				}
				$i++;
				if($i%$labelperpage == 0)
				{
					$pagecounter++; // Set to use new page
					$y = 830; // Set position for first label on new page
				}	
				if(!$orderid)
					continue;
				//$pdf->pages[$pagecounter];
				$shipments = $this->_objectManager->create('\Magento\Sales\Model\ResourceModel\Order\Shipment\Collection')->setOrderFilter($orderid)->load();
				if ($shipments->getSize())
				{
						$flag = true;
						foreach ($shipments as $shipment)
			  			{	
			  				if(($shipment->getOrder()!=NULL)&&($shipment->getIncrementId()==$shipment_id)){
			  					$this->_objectManager->get('\Ced\Delhivery\Model\MenifestoLabel')->getContent($pdf->pages[$pagecounter], $shipment->getEntityId(), $_wayBills, $shipment->getOrder(),$y);
			  				}	
			  			}
				}
				
				$y = $y-190;
							
			}
				
			if ($flag)
			{
				
				$date = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');
		                return $this->_fileFactory->create(
		                    'shippinglabel'. $date . '.pdf',
		                    $pdf->render(),
		                    DirectoryList::VAR_DIR,
		                    'application/pdf'
		                );	 
			} 
			else
			{
				$this->messageManager->addErrorMessage(__('There are no printable shipping labels related to selected waybills.'));
				$this->_redirect('*/grid/awb');
			}
		}
		$this->_redirect('*/grid/awb');
    	
    }


}
