<?php

namespace Cminds\Marketplace\Controller\Adminhtml\Suppliers;

use Cminds\Marketplace\Block\Adminhtml\Supplier\Customfields\Form
    as CustomfieldsForm;
use Cminds\Marketplace\Model\Fields as CustomFields;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;

class Deletecustomfield extends Action
{
    protected $resultPageFactory;
    protected $customfieldsForm;
    protected $_fields;
    protected $_registry;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CustomfieldsForm $customfieldsForm,
        CustomFields $fields,
        Registry $registry
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->customfieldsForm = $customfieldsForm;
        $this->_fields = $fields;
        $this->_registry = $registry;
    }

    public function execute()
    {
        $fieldId = $this->getRequest()->getParam('id', false);
        if ($fieldId) {
            $field = $this->_fields;
            $field->load($fieldId);

            if (!$field->getId()) {
                $this->messageManager->addError(
                    __('This field no longer exists.')
                );
            }

            try {
                $field->delete();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError(
                    __('Can not delete this field.')
                );
            }
        }

        return $this->_redirect(
            '*/*/fields'
        );
    }
}
