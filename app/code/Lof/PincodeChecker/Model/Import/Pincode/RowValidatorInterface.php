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
namespace Lof\PincodeChecker\Model\Import\Pincode;

interface RowValidatorInterface extends \Magento\Framework\Validator\ValidatorInterface
{
       const ERROR_INVALID_PINCODE= 'InvalidValuePINCODE';
       const ERROR_PINCODE_IS_EMPTY = 'EmptyPINCODE';
    /**
     * Initialize validator
     *
     * @return $this
     */
    public function init($context);
}
