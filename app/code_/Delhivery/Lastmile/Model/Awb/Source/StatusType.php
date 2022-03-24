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

class StatusType implements \Magento\Framework\Option\ArrayInterface
{
    const DL = "DL";
    const RT = "RT";
    const UD = "UD";

    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::DL,
                'label' => __('DL')
            ],
            [
                'value' => self::RT,
                'label' => __('RT')
            ],
            [
                'value' => self::UD,
                'label' => __('UD')
            ],
        ];
        return $options;

    }
}
