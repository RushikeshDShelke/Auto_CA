<?php

namespace Cminds\Marketplace\Controller\Invoice;

use Cminds\Marketplace\Controller\AbstractController;
use Cminds\Marketplace\Helper\Data as MarketplaceHelper;
use Cminds\Supplierfrontendproductuploader\Helper\Data as SupplierHelper;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\ItemFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Store\Model\StoreManagerInterface;

class Save extends AbstractController
{
    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var MarketplaceHelper
     */
    protected $marketplaceHelper;

    /**
     * @var InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * @var SupplierHelper
     */
    protected $supplierHelper;

    public function __construct(
        Context $context,
        Transaction $transaction,
        MarketplaceHelper $marketplaceHelper,
        SupplierHelper $supplierHelper,
        CustomerSession $customerSession,
        InvoiceService $invoiceService,
        OrderFactory $orderFactory,
        InvoiceSender $invoiceSender,
        ItemFactory $itemFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct(
            $context,
            $supplierHelper,
            $storeManager,
            $scopeConfig
        );

        $this->transaction = $transaction;
        $this->orderFactory = $orderFactory;
        $this->itemFactory = $itemFactory;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->invoiceSender = $invoiceSender;
        $this->customerSession = $customerSession;
        $this->invoiceService = $invoiceService;
    }

    public function execute()
    {
        if (!$this->canAccess()) {
            return $this->redirectToLogin();
        }
        $post = $this->_request->getParams();
        try {
            $transaction = $this->transaction;
            $order = $this->orderFactory->create()->load($post['order_id']);
            foreach ($post['product'] as $itemId => $qty) {
                if ($qty <= 0) {
                    unset($post['product'][$itemId]);
                }
                $itemModel = $this->itemFactory->create()->load($itemId);
                $isOwner = $this->marketplaceHelper
                    ->isOwner($itemModel->getProductId());
                if (!$itemModel->getProductId() || !$isOwner) {
                    throw new LocalizedException(__('You cannot ship non-owning products'));
                }
                $orderedQty = $itemModel->getQtyInvoiced() + intval($qty);
                if ($itemModel->getQtyOrdered() < $orderedQty) {
                    throw new LocalizedException(
                        __('You cannot invoice more products than it was ordered')
                    );
                }
            }
            foreach ($order->getItems() as $item) {
                if (!isset($post['product'][$item->getId()])) {
                    $post['product'][$item->getId()] = 0;
                }
            }
            if ($order->getState() == 'canceled') {
                throw new LocalizedException(__('You cannot create invoice for canceled order'));
            }
            $invoice = $this->invoiceService->prepareInvoice(
                $order,
                $post['product']
            );
            $invoice->register();
            $invoice->getOrder();
            foreach ($invoice->getAllItems() as $item) {
                $orderItem = $this->itemFactory
                    ->create()
                    ->load($item->getOrderItemId());
                $orderItem->setQtyInvoiced(
                    $item->getQty() + $orderItem->getQtyInvoiced()
                );
                $transaction->addObject($orderItem);
            }
            $loggedUser = $this->customerSession;
            $customer = $loggedUser->getCustomer();
            $comment = $customer->getFirstname() . ' '
                . $customer->getLastname()
                . ' (#' . $customer->getId() . ') created invoice for '
                . count($post['product']) . ' item(s)';
            $order->addStatusHistoryComment($comment);
            $fullyInvoiced = true;
            foreach ($order->getAllItems() as $item) {
                if ($item->getQtyToInvoiced() > 0) {
                    $fullyInvoiced = false;
                }
            }
            if ($fullyInvoiced) {
                if ($order->getState() != Order::STATE_PROCESSING) {
                    $state = Order::STATE_PROCESSING;
                    $order->setState($state, true);
                }
            }
            $transaction->addObject($invoice);
            $transaction->addObject($order);
            $transaction->save();

            if (isset($post['notify_customer']) && (int)$post['notify_customer'] === 1) {
                $this->invoiceSender->send($invoice);
            }

            $this->messageManager->addSuccess(
                'Invoice for order #' . $order->getIncrementId() . ' was created'
            );
            $this->_redirect(
                '*/order/view/',
                ['id' => $post['order_id'], 'tab' => 'invoice']
            );
        } catch (LocalizedException $e) {
            if (null !== $order->getIncrementId()) {
                $order
                    ->addStatusHistoryComment(
                        'Failed to create invoice - ' . $e->getMessage()
                    )
                    ->save();
            }
            $this->messageManager->addError($e->getMessage());
            $this->_redirect(
                '*/invoice/create/',
                ['id' => $post['order_id'], 'tab' => 'invoice']
            );
        }
    }
}
