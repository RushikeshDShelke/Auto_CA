<?php
namespace Kellton\Custominvoice\Block\Adminhtml\Customcminvoice;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Kellton\Custominvoice\Model\customcminvoiceFactory
     */
    protected $_customcminvoiceFactory;

    /**
     * @var \Kellton\Custominvoice\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Kellton\Custominvoice\Model\customcminvoiceFactory $customcminvoiceFactory
     * @param \Kellton\Custominvoice\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Kellton\Custominvoice\Model\CustomcminvoiceFactory $CustomcminvoiceFactory,
        \Kellton\Custominvoice\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_customcminvoiceFactory = $CustomcminvoiceFactory;
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
        $this->setDefaultSort('cm_incr_id');
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
        $collection = $this->_customcminvoiceFactory->create()->getCollection();
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
            'cm_incr_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'cm_incr_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );


		
				$this->addColumn(
					'invoice_number',
					[
						'header' => __('Invoice Number'),
						'index' => 'invoice_number',
					]
				);
				
				$this->addColumn(
					'cm_prefix',
					[
						'header' => __('CM Prefix'),
						'index' => 'cm_prefix',
					]
				);
				
				$this->addColumn(
					'custom_invoice_number',
					[
						'header' => __('Custom Invoice Number'),
						'index' => 'custom_invoice_number',
					]
				);
				
				$this->addColumn(
					'created_at',
					[
						'header' => __('Created At'),
						'index' => 'created_at',
						'type'      => 'datetime',
					]
				);

					


		

		
		   $this->addExportType($this->getUrl('custominvoice/*/exportCsv', ['_current' => true]),__('CSV'));
		   $this->addExportType($this->getUrl('custominvoice/*/exportExcel', ['_current' => true]),__('Excel XML'));

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

        $this->setMassactionIdField('cm_incr_id');
        //$this->getMassactionBlock()->setTemplate('Kellton_Custominvoice::customcminvoice/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('customcminvoice');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('custominvoice/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('custominvoice/*/massStatus', ['_current' => true]),
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
        return $this->getUrl('custominvoice/*/index', ['_current' => true]);
    }

    /**
     * @param \Kellton\Custominvoice\Model\customcminvoice|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		return $this->getUrl(
            'custominvoice/*/edit',
            ['cm_incr_id' => $row->getId()]
        );
    }

	

}
