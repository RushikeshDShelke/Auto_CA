<?php

namespace Cminds\Marketplace\Controller\Shipment;

use Cminds\Marketplace\Controller\AbstractController;
use Cminds\Supplierfrontendproductuploader\Helper\Data as SupplierHelper;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\StoreManagerInterface;

class SaveTracking extends AbstractController
{
    /**
     * @var OrderFactory
     */
    protected $orderFactory;

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
        SupplierHelper $supplierHelper,
        OrderFactory $orderFactory,
        ShipmentFactory $shipmentFactory,
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

        $this->orderFactory = $orderFactory;
        $this->shipmentFactory = $shipmentFactory;
        $this->trackFactory = $trackFactory;
    }

    public function execute()
    {
        if (!$this->canAccess()) {
            return $this->redirectToLogin();
        }

        $post = $this->getRequest()->getParams();

        try {
            $order = $this->orderFactory->create()->load($post['order_id']);
            if (!$order->getId()) {
                throw new LocalizedException(__('Order does not exists.'));
            }
            $shipment = $this->shipmentFactory->create($order)->load($post['shipment_id']);

            if (!$shipment->getId()) {
                throw new LocalizedException(__('Shipment does not exists.'));
            }
            if (!empty($post['number'])) {
                if (!isset($post['track_id'])) {
                    $this->trackFactory
                        ->create()
                        ->setShipment($shipment)
                        ->setTitle($post['title'])
                        ->setTrackNumber($post['number'])
                        ->setCarrierCode($post['carrier_code'])
                        ->setOrderId($post['order_id'])
                        ->save();
                } else {
                    $this->trackFactory
                        ->create()
                        ->load($post['track_id'])
                        ->setTrackNumber($post['number'])
                        ->save();
                }
            }
            $this->messageManager->addSuccess(
                __('Tracking for order #' . $order->getIncrementId() . ' was saved.')
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $this->_redirect(
            '*/shipment/view/',
            ['id' => $post['shipment_id']]
        );
    }
}
