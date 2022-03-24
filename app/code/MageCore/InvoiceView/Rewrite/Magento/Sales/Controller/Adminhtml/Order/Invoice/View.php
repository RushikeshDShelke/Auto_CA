<?php
/**
 * Copyright © Kellton Tech Pvt. Ltd All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MageCore\InvoiceView\Rewrite\Magento\Sales\Controller\Adminhtml\Order\Invoice;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;

class View extends \Magento\Sales\Controller\Adminhtml\Order\Invoice\View
{
	/**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $registry, $resultForwardFactory, $resultPageFactory);
    }

    /**
     * Invoice information page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    // public function execute()
    // {
    //     $invoice = $this->getInvoice();
    //     if (!$invoice) {
    //         /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
    //         $resultForward = $this->resultForwardFactory->create();
    //         return $resultForward->forward('noroute');
    //     }

    //     /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
    //     $resultPage = $this->resultPageFactory->create();
    //     $resultPage->setActiveMenu('Magento_Sales::sales_order');
    //     $resultPage->getConfig()->getTitle()->prepend(__('Invoices'));
    //     $resultPage->getConfig()->getTitle()->prepend(sprintf("#%s", $invoice->getIncrementId()));
    //     $resultPage->getLayout()->getBlock(
    //         'sales_invoice_view'
    //     )->updateBackButtonUrl(
    //         $this->getRequest()->getParam('come_from')
    //     );
    //     return $resultPage;
    // }

    /**
     * Custom Invoice information page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $invoice = $this->getInvoice();
        if (!$invoice) {
            /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('noroute');
        }

        /**Custom Code for setting variable templates */
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $invoiceId = $invoice->getIncrementId();
        
        if ($invoice->getIncrementId()) {
            $objCustominvoice = $objectManager->get('Kellton\Custominvoice\Model\Customcinvoice')->load($invoiceId, 'invoice_number');
            if ($objCustominvoice->getCIncrId())
                $invoiceId = $objCustominvoice->getCustomInvoiceNumber();
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Sales::sales_order');
        $resultPage->getConfig()->getTitle()->prepend(__('Invoices'));
        /** $resultPage->getConfig()->getTitle()->prepend(sprintf("#%s", $invoice->getIncrementId())); */
        if($invoiceId){
            $resultPage->getConfig()->getTitle()->prepend(sprintf("#%s || Custom Invoice Number: #%s", $invoice->getIncrementId(), $invoiceId));
        }else{
            $resultPage->getConfig()->getTitle()->prepend(sprintf("#%s", $invoice->getIncrementId()));
        }
        $resultPage->getLayout()->getBlock(
            'sales_invoice_view'
        )->updateBackButtonUrl(
            $this->getRequest()->getParam('come_from')
        );
        return $resultPage;
    }

}

