<?php
namespace Kellton\Advancecontacts\Block\Adminhtml\Advancecontact;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Kellton\Advancecontacts\Model\advancecontactFactory
     */
    protected $_advancecontactFactory;

    /**
     * @var \Kellton\Advancecontacts\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Kellton\Advancecontacts\Model\advancecontactFactory $advancecontactFactory
     * @param \Kellton\Advancecontacts\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Kellton\Advancecontacts\Model\AdvancecontactFactory $AdvancecontactFactory,
        \Kellton\Advancecontacts\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_advancecontactFactory = $AdvancecontactFactory;
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
        $collection = $this->_advancecontactFactory->create()->getCollection();
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
					'first_name',
					[
						'header' => __('First Name'),
						'index' => 'first_name',
					]
				);
				
				$this->addColumn(
					'last_name',
					[
						'header' => __('Last Name'),
						'index' => 'last_name',
					]
				);
				
				$this->addColumn(
					'email_id',
					[
						'header' => __('Email Id'),
						'index' => 'email_id',
					]
				);
				
				$this->addColumn(
					'telephone',
					[
						'header' => __('Phone Number'),
						'index' => 'telephone',
					]
				);
				

						$this->addColumn(
							'category',
							[
								'header' => __('Select Category'),
								'index' => 'category',
								'type' => 'options',
								'options' => \Kellton\Advancecontacts\Block\Adminhtml\Advancecontact\Grid::getOptionArray4()
							]
						);

				$this->addColumn(
                    'description',
                    [
                        'header' => __('Description'),
                        'index' => 'description',
                    ]
                );	

                $this->addColumn(
                    'created_date',
                    [
                        'header' => __('Created date'),
                        'index' => 'created_date',
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
                        //'field' => 'id'
                    //]
                //],
                //'filter' => false,
                //'sortable' => false,
                //'index' => 'stores',
                //'header_css_class' => 'col-action',
                //'column_css_class' => 'col-action'
            //]
        //);
		

		
		   $this->addExportType($this->getUrl('advancecontacts/*/exportCsv', ['_current' => true]),__('CSV'));
		   $this->addExportType($this->getUrl('advancecontacts/*/exportExcel', ['_current' => true]),__('Excel XML'));

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
        //$this->getMassactionBlock()->setTemplate('Kellton_Advancecontacts::advancecontact/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('advancecontact');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('advancecontacts/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('advancecontacts/*/massStatus', ['_current' => true]),
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
        return $this->getUrl('advancecontacts/*/index', ['_current' => true]);
    }

    /**
     * @param \Kellton\Advancecontacts\Model\advancecontact|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'advancecontacts/*/edit',
            ['id' => $row->getId()]
        );
		
    }

	
		static public function getOptionArray4()
		{
            $data_array=array(); 
			$data_array[0]='Product Related';
			$data_array[1]='Category Related';
			$data_array[2]='Order Related';
			$data_array[3]='Others';
            return($data_array);
		}
		static public function getValueArray4()
		{
            $data_array=array();
			foreach(\Kellton\Advancecontacts\Block\Adminhtml\Advancecontact\Grid::getOptionArray4() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);
			}
            return($data_array);

		}
		

}