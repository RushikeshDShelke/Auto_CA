<?php
/**
 * Delhivery_Lastmile extension
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category  Delhivery
 * @package   Delhivery_Lastmile
 * @copyright Copyright (c) 2018
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Delhivery\Lastmile\Model\Awb\Source;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    const ASSIGNED = "Assigned";
    const INTRANSIT = 'InTransit';
    const DISPATCHED = 'Dispatched';
    const PENDING = 'Pending';
    const PRELOAD = 'Preload';
    const DELIVERED = 'Delivered';
    const RETURNED = 'Returned';
    const CANCELLED = 'Cancelled';
    const COLLECTED = 'Collected';
    const PICKEDUP = 'Picked-UP';
    const MANIFESTED = 'Manifested';
    const NOTPICKED = 'NotPicked';
    const RTO = "RTO";
    const DL = "DL";
    const UD = "UD";
    const RT = "RT";

    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::ASSIGNED,
                'label' => __('Assigned')
            ],
            [
                'value' => self::INTRANSIT,
                'label' => __('InTransit')
            ],
            [
                'value' => self::DISPATCHED,
                'label' => __('Dispatched')
            ],
            [
                'value' => self::PENDING,
                'label' => __('Pending')
            ],
            [
                'value' => self::PRELOAD,
                'label' => __('Preload')
            ],
            [
                'value' => self::DELIVERED,
                'label' => __('Delivered')
            ],
            [
                'value' => self::RETURNED,
                'label' => __('Returned')
            ],
            [
                'value' => self::CANCELLED,
                'label' => __('Cancelled')
            ],
            [
                'value' => self::COLLECTED,
                'label' => __('Collected')
            ],
            [
                'value' => self::PICKEDUP,
                'label' => __('Picked-UP')
            ],
            [
                'value' => self::MANIFESTED,
                'label' => __('Manifested')
            ],
            [
                'value' => self::NOTPICKED,
                'label' => __('NotPicked')
            ],
            [
                'value' => self::RTO,
                'label' => __('RTO')
            ],
            [
                'value' => self::DL,
                'label' => __('DL')
            ],
            [
                'value' => self::UD,
                'label' => __('UD')
            ],
            [
                'value' => self::RT,
                'label' => __('RT')
            ],
        ];
        return $options;

    }
}
