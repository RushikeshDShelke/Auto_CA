<?php

namespace Kellton\Shipmentdelay\Block\Adminhtml\Shipmentdelay\Edit\Tab;

/**
 * Shipmentdelay edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Kellton\Shipmentdelay\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Kellton\Shipmentdelay\Model\Status $status,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Kellton\Shipmentdelay\Model\BlogPosts */
        $model = $this->_coreRegistry->registry('shipmentdelay');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

		
        $fieldset->addField(
            'orderid',
            'text',
            [
                'name' => 'orderid',
                'label' => __('Order Id'),
                'title' => __('orderid'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'shipmentid',
            'text',
            [
                'name' => 'shipmentid',
                'label' => __('Shipment Id'),
                'title' => __('shipmentid'),
				'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'createdat',
            'text',
            [
                'name' => 'createdat',
                'label' => __('Create Date'),
                'title' => __('createdat'),
				
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'delay',
            'text',
            [
                'name' => 'delay',
                'label' => __('Delay'),
                'title' => __('delay'),
				'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
					

        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('status'),
                'name' => 'status',
				
                'options' => \Kellton\Shipmentdelay\Block\Adminhtml\Shipmentdelay\Grid::getOptionArray5(),
                'disabled' => $isElementDisabled
            ]
        );

						

        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Item Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Item Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    public function getTargetOptionArray(){
    	return array(
    				'_self' => "Self",
					'_blank' => "New Page",
    				);
    }
}
