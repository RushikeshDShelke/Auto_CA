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

namespace Lof\PincodeChecker\Api\Data;

interface PincodecheckerInterface
{

    const PINCODE = 'pincode';
    const PINCODECHECKER_ID = 'pincodechecker_id';


    /**
     * Get pincodechecker_id
     * @return string|null
     */
    
    public function getPincodecheckerId();

    /**
     * Set pincodechecker_id
     * @param string $pincodechecker_id
     * @return Lof\PincodeChecker\Api\Data\PincodecheckerInterface
     */
    
    public function setPincodecheckerId($pincodecheckerId);

    /**
     * Get pincode
     * @return string|null
     */
    
    public function getPincode();

    /**
     * Set pincode
     * @param string $pincode
     * @return Lof\PincodeChecker\Api\Data\PincodecheckerInterface
     */
    
    public function setPincode($pincode);
}
