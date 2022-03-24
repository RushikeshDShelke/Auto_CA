<?php

namespace Cminds\Marketplace\Controller\Adminhtml\Billing;

use Cminds\Marketplace\Model\ResourceModel\Payment as PaymentResource;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{
    /**
     * Page factory object.
     *
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * Payment resource object.
     *
     * @var PaymentResource
     */
    private $paymentResource;

    /**
     * Core registry object.
     *
     * @var Registry
     */
    private $coreRegistry;

    /**
     * Edit constructor.
     *
     * @param Context         $context
     * @param PageFactory     $resultPageFactory
     * @param PaymentResource $paymentResource
     * @param Registry        $coreRegistry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        PaymentResource $paymentResource,
        Registry $coreRegistry
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->paymentResource = $paymentResource;
        $this->coreRegistry = $coreRegistry;
    }

    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $supplierId = $this->getRequest()->getParam('supplier_id');

        if ($supplierId && $orderId) {
            $payment = $this->paymentResource
                ->getSupplierPaymentByOrderId($supplierId, $orderId);

            $this->coreRegistry->register(
                'marketplacepaypal_billing_payment',
                $payment
            );

            $resultPage = $this->resultPageFactory->create();
            $resultPage
                ->setActiveMenu('Cminds_Marketplace::billing_reports');
            $resultPage
                ->getConfig()
                ->getTitle()
                ->prepend(__('Create Manual Payment'));

            return $resultPage;
        }

        $this->messageManager->addErrorMessage(
            __('Selected supplier billing report does not exists.')
        );

        return $this->_redirect('*/*/index');
    }
}
