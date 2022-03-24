<?php

namespace Cminds\Marketplace\Controller\Adminhtml\Suppliers;

use Cminds\Marketplace\Block\Adminhtml\Supplier\Customfields\Form
    as CustomfieldsForm;
use Cminds\Marketplace\Model\Fields as FieldsModel;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Editcustomfield extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    protected $customfieldsForm;
    protected $_fields;
    protected $_registry;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CustomfieldsForm $customfieldsForm,
        FieldsModel $fields,
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
        $field = $this->_fields;
        $fieldId = $this->getRequest()->getParam('id', false);
        if ($fieldId) {
            $field->load($fieldId);

            if (!$field->getId()) {
                $this->messageManager->addError(
                    __('This field no longer exists.')
                );

                return $this->_redirect(
                    '*/*/fields'
                );
            }
        }

        $postData = $this->getRequest()->getParam('fieldData');
        if ($postData) {
            try {
                if (!$field->getId()) {
                    $postData['created_at'] = date('Y-m-d H:i:s');
                }
                $nameExists = $this->_fields->load($postData['name'], 'name');

                if ($nameExists->getId()
                    && !$this->getRequest()->getParam('id', false)
                ) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Field with this name already exists.')
                    );
                }

                $field->addData($postData);
                $field->save();

                $this->messageManager->addSuccess(
                    __('The field has been saved.')
                );

                return $this->_redirect(
                    '*/*/fields',
                    ['id' => $field->getId()]
                );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_registry->register('current_field', $field);

        if (isset($postData)) {
            $this->_registry->register('current_field_post_data', $postData);
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Cminds_Marketplace::profile_fields');
        $resultPage->getConfig()->getTitle()->prepend(__('Custom Field'));

        return $resultPage;
    }
}
