<?php
/**
 * Copyright © Kellton Tech Pvt. Ltd. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MageCore\AdminDashboard\Rewrite\Magento\Reports\Model\ResourceModel\Order;

class Collection extends \Magento\Reports\Model\ResourceModel\Order\Collection
{

	/**
     * Add revenue
     *
     * @param bool $convertCurrency
     * @return $this
     */
    public function addRevenueToSelect($convertCurrency = false)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/testdash.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('collection');

        $expr = $this->getTotalsExpressionWithDiscountRefunded(
            !$convertCurrency,
            $this->getConnection()->getIfNullSql('main_table.base_subtotal_refunded', 0),
            $this->getConnection()->getIfNullSql('main_table.base_subtotal_canceled', 0),
            $this->getConnection()->getIfNullSql('ABS(main_table.base_discount_refunded)', 0),
            $this->getConnection()->getIfNullSql('ABS(main_table.base_discount_canceled)', 0)
        );
        $this->getSelect()->columns(['revenue' => $expr]);
        
        return $this;
    }

	/**
     * Get SQL expression for totals with discount refunded.
     *
     * @param int $storeId
     * @param string $baseSubtotalRefunded
     * @param string $baseSubtotalCanceled
     * @param string $baseDiscountRefunded
     * @param string $baseDiscountCanceled
     * @return string
     */
    private function getTotalsExpressionWithDiscountRefunded(
        $storeId,
        $baseSubtotalRefunded,
        $baseSubtotalCanceled,
        $baseDiscountRefunded,
        $baseDiscountCanceled
    ) {
        $template = ($storeId != 0)
            ? '(main_table.total_due - %2$s - %1$s - (ABS(main_table.base_discount_amount) - %3$s - %4$s))'
            : '((main_table.total_due - %1$s - %2$s - (ABS(main_table.base_discount_amount) - %3$s - %4$s)) '
                . ' * main_table.base_to_global_rate)';
        return sprintf(
            $template,
            $baseSubtotalRefunded,
            $baseSubtotalCanceled,
            $baseDiscountRefunded,
            $baseDiscountCanceled
        );
    }

}

