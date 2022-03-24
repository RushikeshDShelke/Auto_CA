<?php
namespace Kellton\Shipmentdelay\Block\Adminhtml\Shipmentdelay;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Kellton\Shipmentdelay\Model\shipmentdelayFactory
     */
    protected $_shipmentdelayFactory;

    /**
     * @var \Kellton\Shipmentdelay\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Kellton\Shipmentdelay\Model\shipmentdelayFactory $shipmentdelayFactory
     * @param \Kellton\Shipmentdelay\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Kellton\Shipmentdelay\Model\ShipmentdelayFactory $ShipmentdelayFactory,
        \Kellton\Shipmentdelay\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_shipmentdelayFactory = $ShipmentdelayFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_shipmentdelayFactory->create()->getCollection();
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );


		
				$this->addColumn(
					'orderid',
					[
						'header' => __('Order Id'),
						'index' => 'orderid',
					]
				);
				
				$this->addColumn(
					'shipmentid',
					[
						'header' => __('Shipment Id'),
						'index' => 'shipmentid',
					]
				);
				
				$this->addColumn(
					'createdat',
					[
						'header' => __('Create Date'),
						'index' => 'createdat',
					]
				);
				
				$this->addColumn(
					'delay',
					[
						'header' => __('Delay'),
						'index' => 'delay',
					]
				);
				

						$this->addColumn(
							'status',
							[
								'header' => __('Status'),
								'index' => 'status',
								'type' => 'options',
								'options' => \Kellton\Shipmentdelay\Block\Adminhtml\Shipmentdelay\Grid::getOptionArray5()
							]
						);

		
		   $this->addExportType($this->getUrl('shipmentdelay/*/exportCsv', ['_current' => true]),__('CSV'));
		   $this->addExportType($this->getUrl('shipmentdelay/*/exportExcel', ['_current' => true]),__('Excel XML'));

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

	
    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {

        $this->setMassactionIdField('id');
        //$this->getMassactionBlock()->setTemplate('Kellton_Shipmentdelay::shipmentdelay/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('shipmentdelay');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('shipmentdelay/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('shipmentdelay/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses
                    ]
                ]
            ]
        );


        return $this;
    }
		

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('shipmentdelay/*/index', ['_current' => true]);
    }

    /**
     * @param \Kellton\Shipmentdelay\Model\shipmentdelay|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'shipmentdelay/*/edit',
            ['id' => $row->getId()]
        );
		
    }

	
		static public function getOptionArray5()
		{
            $data_array=array(); 
			
			$data_array[1]='Enable';
            $data_array[0]='Disable';
            return($data_array);
		}
		static public function getValueArray5()
		{
            $data_array=array();
			foreach(\Kellton\Shipmentdelay\Block\Adminhtml\Shipmentdelay\Grid::getOptionArray5() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);
			}
            return($data_array);

		}
		

}