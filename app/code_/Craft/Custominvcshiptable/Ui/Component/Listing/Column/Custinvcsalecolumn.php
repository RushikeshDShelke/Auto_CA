<?php
namespace Craft\Custominvcshiptable\Ui\Component\Listing\Column;
 
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
 
class Custinvcsalecolumn extends Column
{
 
    protected $_orderRepository;
    protected $_searchCriteria;
    protected $_customfactory;
 
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $criteria,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        array $components = [], array $data = [])
    {
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        $this->resource = $resource;
        $this->orderFactory = $orderFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
 
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
				$order  = $this->_orderRepository->get($item["entity_id"]);
                    $orderid = $order->getId();
					
					$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
					$order = $objectManager->create('\Magento\Sales\Model\Order')->load($orderid);
					$invoiceCollection = $order->getInvoiceCollection();
					foreach($invoiceCollection as $invoice){
					$custsalecolumn = $invoice->getIncrementId();// invoice increment id
					// same way get other details of invoice
					$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
			$objectManager  =   \Magento\Framework\App\ObjectManager::getInstance();
			$cdat = $objectManager->create('Kellton\Custominvoice\Model\ResourceModel\Customcinvoice\Collection');
			$cdat->addFieldToFilter('invoice_number', array('eq' => $custsalecolumn));
					foreach($cdat as $invicedata){
						$item['custsalecolumn'] = $invicedata['custom_invoice_number'];
					}				
					}
            }
        }
        return $dataSource;
    }
}