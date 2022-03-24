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
namespace Lof\PincodeChecker\Model\ResourceModel\Pincodechecker;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected $_idFieldName = 'pincode_id';

    protected function _construct()
    {
        $this->_init(
            'Lof\PincodeChecker\Model\Pincodechecker',
            'Lof\PincodeChecker\Model\ResourceModel\Pincodechecker'
        );
    }
}
