<?php

namespace Cminds\Marketplace\Controller\Shipment;

use Cminds\Marketplace\Controller\AbstractController;
use Cminds\Marketplace\Helper\Data as MarketplaceHelper;
use Cminds\Supplierfrontendproductuploader\Helper\Data as SupplierHelper;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use Magento\Sales\Model\Order\ItemFactory;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Sales\Model\Order\ShipmentFactory;
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
     * @var ShipmentSender
     */
    protected $shipmentSender;

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

    /**
     * @var ShipmentFactory
     */
    protected $shipmentFactory;

    /**
     * @var TrackFactory
     */
    protected $trackFactory;

    public function __construct(
        Context $context,
        Transaction $transaction,
        MarketplaceHelper $marketplaceHelper,
        SupplierHelper $supplierHelper,
        CustomerSession $customerSession,
        InvoiceService $invoiceService,
        OrderFactory $orderFactory,
        ItemFactory $itemFactory,
        ShipmentFactory $shipmentFactory,
        ShipmentSender $shipmentSender,
        TrackFactory $trackFactory,
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
        $this->shipmentFactory = $shipmentFactory;
        $this->trackFactory = $trackFactory;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->shipmentSender = $shipmentSender;
        $this->customerSession = $customerSession;
        $this->invoiceService = $invoiceService;
    }

    public function execute()
    {
        if (!$this->canAccess()) {
            return $this->redirectToLogin();
        }

        $postData = $this->getRequest()->getParams();
        $order = null;

        try {
            $order = $this->orderFactory->create()
                ->load($postData['order_id']);

            $selectedItems = isset($postData['product']) ? $postData['product'] : [];
            unset($postData['product']);

            foreach ($selectedItems as $itemId => $qty) {
                if ($qty <= 0) {
                    unset($selectedItems[$itemId]);
                }
            }

            if (count($selectedItems) === 0) {
                throw new LocalizedException(
                    __('Please type proper qty values for items to be shipped.')
                );
            }

            if ($order->getState() === 'canceled') {
                throw new LocalizedException(
                    __('You cannot create shipment for canceled order.')
                );
            }

            foreach ($selectedItems as $itemId => $qty) {
                $itemModel = $this->itemFactory->create()
                    ->load($itemId);
                $isOwner = $this->marketplaceHelper
                    ->isOwner($itemModel->getProductId());
                if (!$isOwner || !$itemModel->getProductId()) {
                    throw new LocalizedException(
                        __('You cannot ship non-owning products.')
                    );
                }

                $orderedQty = $itemModel->getQtyShipped() + (int)$qty;
                if ($itemModel->getQtyOrdered() < $orderedQty) {
                    throw new LocalizedException(
                        __('You cannot ship more products than it was ordered.')
                    );
                }
            }

            $shipment = $this->shipmentFactory->create(
                $order,
                $selectedItems
            );
            $shipment->register();
            $shipment->save();

            $createdShipment = $this->shipmentFactory->create($order)
                ->load($shipment->getEntityId());
            foreach ($createdShipment->getAllItems() as $item) {
                $orderItem = $this->itemFactory
                    ->create()
                    ->load($item->getOrderItemId());
                $orderItem->setQtyShipped(
                    (string)($item->getQty() + $orderItem->getQtyShipped())
                );
                $this->transaction->addObject($orderItem);
            }

            if (!empty($postData['number']) && !empty($postData['carrier_code'])) {
                $sh = $this->trackFactory
                    ->create()
                    ->setShipment($createdShipment)
                    ->setTitle($postData['title'])
                    ->setTrackNumber($postData['number'])
                    ->setCarrierCode($postData['carrier_code'])
                    ->setOrderId($postData['order_id']);

                $this->transaction->addObject($sh);
            }

            $loggedUser = $this->customerSession;
            $customer = $loggedUser->getCustomer();
            $comment = $customer->getFirstname() . ' '
                . $customer->getLastname() . ' (#' . $customer->getId()
                . ') created shipment for ' . count($selectedItems)
                . ' item(s)';
            $order->addStatusHistoryComment($comment);
            $fullyShipped = true;

            foreach ($order->getAllItems() as $item) {
                if ($item->getQtyToShip() > 0 && !$item->getIsVirtual()
                    && !$item->getLockedDoShip()
                ) {
                    $fullyShipped = false;
                }
            }

            if ($fullyShipped) {
                if ($order->getState() !== Order::STATE_PROCESSING) {
                    $state = Order::STATE_PROCESSING;
                } else {
                    $state = Order::STATE_COMPLETE;
                }
                if ($state) {
                    $order->setData('state', $state);
                    $status = $order->getConfig()->getStateDefaultStatus($state);
                    $order->setStatus($status);
                    $order->addStatusHistoryComment($comment, false);
                }
            }

            $this->transaction->addObject($order);
            $this->transaction->save();

            if (isset($postData['notify_customer']) && (int)$postData['notify_customer'] === 1) {
                $this->shipmentSender->send($shipment);
            }
            $this->messageManager->addSuccessMessage(
                'Shipment for order #' . $order->getIncrementId() . ' was created'
            );

            return $this->_redirect(
                '*/order/view/',
                ['id' => $postData['order_id'], 'tab' => 'shipment']
            );
        } catch (LocalizedException $e) {
            if ($order && $order->getIncrementId()) {
                $order
                    ->addStatusHistoryComment(
                        'Failed to create shipment - ' . $e->getMessage()
                    )
                    ->save();
            }

            $this->messageManager->addErrorMessage($e->getMessage());

            return $this->_redirect(
                '*/shipment/create/',
                ['id' => $postData['order_id'], 'tab' => 'shipment']
            );
        }
    }
}
