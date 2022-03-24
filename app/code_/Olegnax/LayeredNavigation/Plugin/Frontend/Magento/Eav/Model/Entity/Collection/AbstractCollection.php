<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\LayeredNavigation\Plugin\Frontend\Magento\Eav\Model\Entity\Collection;

use Closure;
use Exception;
use Magento\Framework\DataObject;

class AbstractCollection
{

    /**
     * @param \Magento\Eav\Model\Entity\Collection\AbstractCollection $subject
     * @param Closure $process
     * @param DataObject $dataObject
     * @return $this
     */
    public function aroundAddItem(
        \Magento\Eav\Model\Entity\Collection\AbstractCollection $subject,
        Closure $process,
        DataObject $dataObject
    ) {
        try {
            return $process($dataObject);
        } catch (Exception $e) {
            return $this;
        }
    }
}