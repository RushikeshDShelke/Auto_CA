<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Craftmaestros\CancelOrderReversePromotions\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Model\ResourceModel\Coupon\Usage;
use Magento\SalesRule\Model\Rule\CustomerFactory;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

/**
 * Class SalesOrderAfterCancelObserver
 *
 * @package Magento\SalesRule\Observer
 */
class Cancelpromotions implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var Coupon
     */
    public $coupon;

    /**
     * @var \Magento\SalesRule\Api\CouponRepositoryInterface
     */
    protected $couponRepository;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon\Usage
     */
    protected $couponUsage;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * SalesOrderAfterCancelObserver constructor.
     *
     * @param \Magento\SalesRule\Model\Coupon                     $coupon
     * @param \Magento\SalesRule\Api\CouponRepositoryInterface    $couponRepository
     * @param \Magento\SalesRule\Model\ResourceModel\Coupon\Usage $couponUsage
     * @param \Magento\SalesRule\Model\Rule\CustomerFactory       $customerFactory
     * @param \Magento\Framework\App\ResourceConnection           $resourceConnection
     * @param \Psr\Log\LoggerInterface                            $logger
     */
    public function __construct(
        Coupon $coupon,
        CouponRepositoryInterface $couponRepository,
        Usage $couponUsage,
        CustomerFactory $customerFactory,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->coupon = $coupon;
        $this->couponRepository = $couponRepository;
        $this->couponUsage = $couponUsage;
        $this->customerFactory = $customerFactory;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $observer->getOrder();
	$objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
	$StockState             = $objectManager->get('\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');
	foreach($order->getAllVisibleItems() as $item)
	{
		$qty                    = $StockState->execute($item->getSku());
        	$total_salable_qty      = $qty[0]['qty'];
		$totalQty =0;
		//$baseUrlVar             = 'http://efuat.craftmaestros.com/';
		$baseUrlVar             = 'https://www.earthfables.com/';
        	//exec ("curl --data 'sku=".$item->getSku()."&qty=".(int)$total_salable_qty."' https://earth-fables.craftmaestros.com/ProductQtySyncCraft.php");
		exec ("curl --data 'sku=".$item->getSku()."&salableqty=".(int)$total_salable_qty."&mainqty=".$totalQty."&event=order_canceled&orderid=".$order->getId()."' ".$baseUrlVar."ProductQtySyncCraftSalable.php");
		$resource       = $objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection     = $resource->getConnection();
                $sku_history = $resource->getTableName('sku_inventory_history'); //gives table name with prefix
                date_default_timezone_set('Asia/Kolkata');
                $insertData     = ["sku"=>$item->getSku(),
                                         "reduced_qty"=> 0,
                                         "increased_qty"=> $item->getQtyOrdered(),
                                         "updated_salable_qty" => $total_salable_qty,
                                         "action_comment"=>"Order Canceled : ".$order->getIncrementId(),
                                         "created_at" =>date("Y-m-d h:i:s")
                                         ];
                $result = $connection->insert($sku_history, $insertData);
                date_default_timezone_set('UTC');	
	}
        $customerId = $order->getCustomerId();
	if($order->getAppliedRuleIds())
	{
		$salesruleIds = explode(',', $order->getAppliedRuleIds());
		foreach($salesruleIds as $saleruleId)
		{
			if ($customerId !== null && !empty($saleruleId)) {
                		$this->restoreRule($saleruleId, $customerId);
			}
		}
	}

        return $this;
    }

    /**
     * Restore coupon
     *
     * @param Coupon $coupon
     * @param int    $customerId
     */
    protected function restoreRule($ruleId, $customerId)
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();
        try {

            /** @var \Magento\SalesRule\Model\Rule\Customer $ruleCustomer */
            $ruleCustomer = $this->customerFactory->create();
            $ruleCustomer->loadByCustomerRule($customerId, $ruleId);
            if ($ruleCustomer->getId()) {
                $ruleCustomer->setTimesUsed($ruleCustomer->getTimesUsed() - 1);
                $ruleCustomer->save();
            }

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->logger->critical($e);
        }
    }
}
