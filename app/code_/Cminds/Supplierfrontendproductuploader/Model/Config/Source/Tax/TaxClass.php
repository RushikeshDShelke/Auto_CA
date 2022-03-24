<?php

namespace Cminds\Supplierfrontendproductuploader\Model\Config\Source\Tax;

use Magento\Tax\Model\ResourceModel\TaxClass\Collection;

class TaxClass
{
    public function __construct(Collection $collection)
    {
        $this->_collection = $collection;
    }

    public function toOptionArray()
    {
        $collection = $this->_collection->toOptionArray();

        return $collection;
    }
}
