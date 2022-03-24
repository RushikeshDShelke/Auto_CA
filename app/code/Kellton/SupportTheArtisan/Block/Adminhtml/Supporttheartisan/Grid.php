<?php
namespace Kellton\SupportTheArtisan\Block\Adminhtml\Supporttheartisan;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Kellton\SupportTheArtisan\Model\supporttheartisanFactory
     */
    protected $_supporttheartisanFactory;

    /**
     * @var \Kellton\SupportTheArtisan\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Kellton\SupportTheArtisan\Model\supporttheartisanFactory $supporttheartisanFactory
     * @param \Kellton\SupportTheArtisan\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Kellton\SupportTheArtisan\Model\SupporttheartisanFactory $SupporttheartisanFactory,
        \Kellton\SupportTheArtisan\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_supporttheartisanFactory = $SupporttheartisanFactory;
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
        $this->setDefaultSort('entity_id');
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
        $collection = $this->_supporttheartisanFactory->create()->getCollection();
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
            'entity_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
            $this->addColumn(
            'transaction_id',
            [
                'header' => __('Transaction ID'),
                'type' => 'transaction_id',
                'index' => 'transaction_id'
                
            ]
        );


		
				$this->addColumn(
					'customer_name',
					[
						'header' => __('Name'),
						'index' => 'customer_name',
					]
				);
				
				$this->addColumn(
					'email_id',
					[
						'header' => __('Email'),
						'index' => 'email_id',
					]
				);
				
				$this->addColumn(
					'phone_no',
					[
						'header' => __('Phone No'),
						'index' => 'phone_no',
					]
				);
				
				$this->addColumn(
					'category_name',
					[
						'header' => __('Category'),
						'index' => 'category_name',
					]
				);
				
				$this->addColumn(
					'amount',
					[
						'header' => __('Amount'),
						'index' => 'amount',
					]
				);
				

						$this->addColumn(
							'status',
							[
								'header' => __('Status'),
								'index' => 'status',
								'type' => 'options',
								'options' => \Kellton\SupportTheArtisan\Block\Adminhtml\Supporttheartisan\Grid::getOptionArray5()
							]
						);

				$this->addColumn(
                    'customer_address',
                    [
                        'header' => __('Address'),
                        'index' => 'customer_address'
                        
                    ]
                );		
				$this->addColumn(
					'created_date',
					[
						'header' => __('Date'),
						'index' => 'created_date',
						'type'      => 'datetime',
					]
				);

					


		
        //$this->addColumn(
            //'edit',
            //[
                //'header' => __('Edit'),
                //'type' => 'action',
                //'getter' => 'getId',
                //'actions' => [
                    //[
                        //'caption' => __('Edit'),
                        //'url' => [
                            //'base' => '*/*/edit'
                        //],
                        //'field' => 'entity_id'
                    //]
                //],
                //'filter' => false,
                //'sortable' => false,
                //'index' => 'stores',
                //'header_css_class' => 'col-action',
                //'column_css_class' => 'col-action'
            //]
        //);
		

		
		   $this->addExportType($this->getUrl('supporttheartisan/*/exportCsv', ['_current' => true]),__('CSV'));
		   $this->addExportType($this->getUrl('supporttheartisan/*/exportExcel', ['_current' => true]),__('Excel XML'));

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

        $this->setMassactionIdField('entity_id');
        //$this->getMassactionBlock()->setTemplate('Kellton_SupportTheArtisan::supporttheartisan/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('supporttheartisan');

        

        $statuses = $this->_status->getOptionArray();
       // $statuses = array(['pending']=>'Pending',['Failed']=>'Failed', ['Success']=>'Success', ['Cancel']=>'Cancel');

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('supporttheartisan/*/massStatus', ['_current' => true]),
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
        return $this->getUrl('supporttheartisan/*/index', ['_current' => true]);
    }

    /**
     * @param \Kellton\SupportTheArtisan\Model\supporttheartisan|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'supporttheartisan/*/edit',
            ['entity_id' => $row->getId()]
        );
		
    }

	
		static public function getOptionArray5()
		{
            $data_array=array(); 
			$data_array['pending']='Pending';
			$data_array['Success']='Success';
			$data_array['Cancel']='Cancel';
			$data_array['Failed']='Failed';
            return($data_array);
		}
		static public function getValueArray5()
		{
            $data_array=array();
			foreach(\Kellton\SupportTheArtisan\Block\Adminhtml\Supporttheartisan\Grid::getOptionArray5() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);
			}
            return($data_array);

		}
		

}