<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Lof
 * @package   Lof\PincodeChecker
 * @author    Landofcoder <landofcoder@gmail.com>
 * @copyright 2020 Landofcoder
 * @license   https://landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\PincodeChecker\Model;

use Lof\PincodeChecker\Api\Data\PincodecheckerInterface;

class Pincodechecker extends \Magento\Framework\Model\AbstractModel implements PincodecheckerInterface
{

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Lof\PincodeChecker\Model\ResourceModel\Pincodechecker');
    }

    /**
     * Get PINCODE_ID
     * @return string
     */
    public function getPincodecheckerId()
    {
        return $this->getData(self::PINCODE_ID);
    }

    /**
     * Set PINCODE_ID
     * @param string $pincodecheckerId
     * @return Lof\PincodeChecker\Api\Data\PincodecheckerInterface
     */
    public function setPincodecheckerId($pincodecheckerId)
    {
        return $this->setData(self::PINCODE_ID, $pincodecheckerId);
    }

    /**
     * Get pincode
     * @return string
     */
    public function getPincode()
    {
        return $this->getData(self::PINCODE);
    }

    /**
     * Set pincode
     * @param string $pincode
     * @return Lof\PincodeChecker\Api\Data\PincodecheckerInterface
     */
    public function setPincode($pincode)
    {
        return $this->setData(self::PINCODE, $pincode);
    }
}
