<?php
namespace Delhivery\Lastmile\Block\Adminhtml\Location;

use Delhivery\Lastmile\Model\LocationFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;

/**
 * Class Grid
 * @package Delhivery\Lastmile\Block\Adminhtml\Location
 */
class Grid extends Extended
{
    /**
     * @var NewspostFactory
     */
    protected $_locationFactory;

    /**
     * @var Registry
     */
    protected $_coreRegistry;
    /**
     * @var Status
     */

    /**
     * Grid constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Data $backendHelper
     * @param NewspostFactory $newspostFactory
     * @param Status $status
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Data $backendHelper,
        LocationFactory $locationFactory,
		\Magento\Framework\Stdlib\DateTime\DateTime $datetime,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        array $data = []
    ) {
        $this->timezone = $timezone;
		$this->datetime = $datetime;
        $this->_locationFactory = $locationFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('LocationGrid');
        $this->setDefaultSort('location_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
		$this->setFilterVisibility(false);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_locationFactory->create()->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'location_id',
            [
                'header' => __('Id'),
                'type' => 'number',
                'index' => 'location_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name',
                'class' => 'xxx',
            ]
        );
		$this->addColumn(
            'address',
            [
                'header' => __('Address'),
                'index' => 'address',
                'class' => 'xxx',
                'width' => '150px',
            ]
        );
		$this->addColumn(
            'contact_person',
            [
                'header' => __('Contact Person'),
                'index' => 'contact_person',
                'class' => 'xxx',
            ]
        );
		$this->addColumn(
            'email',
            [
                'header' => __('Email'),
                'index' => 'email',
                'class' => 'xxx',
            ]
        );
		$this->addColumn(
            'pin',
            [
                'header' => __('Pin'),
                'index' => 'pin',
                'class' => 'xxx',
            ]
        );
		$this->addColumn(
            'state',
            [
                'header' => __('State'),
                'index' => 'state',
                'class' => 'xxx',
            ]
        );
		$this->addColumn(
            'expected_package_count',
            [
                'header' => __('Expected Package Count'),
                'index' => 'expected_package_count',
                'class' => 'xxx',
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('location_id');
        $this->getMassactionBlock()->setFormFieldName('location');
        
		$hours=array();
		for($ii=1;$ii<=24;$ii++){
			$hours[]=array('value'=>str_pad($ii,2,'0',STR_PAD_LEFT),'label' => str_pad($ii,2,'0',STR_PAD_LEFT));
		}
		$outputFormat = $this->datetime->date($this->timezone->getDateFormat(\IntlDateFormatter::MEDIUM));
        $this->getMassactionBlock()->addItem('create_pickup_request', array(
            'label' => 'Create Pickup Request',
            'url' => $this->getUrl('*/*/createPickUp', array('_current' => true)),
            //'confirm' => Mage::helper('lastmile')->__('Are you sure?')
			'additional'   => array('pickup_date_time' => array(
									'name'   => 'pickup_date_time',
									'type'      => 'text',
									'width'     => '80px',
									'required' => true,
									'placeholder' => "mm/dd/yyyy",
									'class'=> 'required-entry',
									'gmtoffset' => true,
									'value' => date('m/d/Y'),
								),
								'pickup_hours'=>array(
									'name' => 'pickup_hours',
									'label' => 'Hours',
									'type' => 'select',
									'values' => $hours
								),
								'pickup_minute'=>array(
									'name' => 'pickup_minute',
									'label' => 'Min.',
									'type' => 'select',
									'values' => array(array('value'=>15,'label'=>15),array('value'=>30,'label'=>30),array('value'=>45,'label'=>45)),
									)
        					)
		));
		
        return $this;
    }
}

