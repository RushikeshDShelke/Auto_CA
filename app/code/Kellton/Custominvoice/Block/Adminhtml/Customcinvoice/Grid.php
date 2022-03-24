<?php
namespace Kellton\Custominvoice\Block\Adminhtml\Customcinvoice;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Kellton\Custominvoice\Model\customcinvoiceFactory
     */
    protected $_customcinvoiceFactory;

    /**
     * @var \Kellton\Custominvoice\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Kellton\Custominvoice\Model\customcinvoiceFactory $customcinvoiceFactory
     * @param \Kellton\Custominvoice\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Kellton\Custominvoice\Model\CustomcinvoiceFactory $CustomcinvoiceFactory,
        \Kellton\Custominvoice\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_customcinvoiceFactory = $CustomcinvoiceFactory;
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
        $this->setDefaultSort('c_incr_id');
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
        $collection = $this->_customcinvoiceFactory->create()->getCollection();
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
            'c_incr_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'c_incr_id',
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
					'c_prefix',
					[
						'header' => __('C Prefix'),
						'index' => 'c_prefix',
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

        $this->setMassactionIdField('c_incr_id');
        //$this->getMassactionBlock()->setTemplate('Kellton_Custominvoice::customcinvoice/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('customcinvoice');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('custominvoice/*/massDelete'),
                'confirm' => __('Are you sure?')
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
     * @param \Kellton\Custominvoice\Model\customcinvoice|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		return $this->getUrl(
            'custominvoice/*/edit',
            ['c_incr_id' => $row->getId()]
        );;
    }

	

}
