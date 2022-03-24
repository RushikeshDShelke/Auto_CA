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

class State implements \Magento\Framework\Option\ArrayInterface
{
    const USED = 1;
    const UNUSED = 2;
	const EXPIRED = 4;

    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::USED,
                'label' => __('Used')
            ],
            [
                'value' => self::UNUSED,
                'label' => __('Unused')
            ],
			[
                'value' => self::EXPIRED,
                'label' => __('Expired')
            ],
        ];
        return $options;

    }
}
