<?php

namespace Cminds\Marketplace\Model\ResourceModel\Report\Bestsellers;

use Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection
    as BestsellersCollection;
use Magento\Framework\DB\Select;

class Collection extends BestsellersCollection
{
    protected function _applyStoresFilterToSelect(
       Select $select
    ) {
        $nullCheck = false;
        $storeIds = $this->_storesIds;

        if (!is_array($storeIds)) {
            $storeIds = [$storeIds];
        }

        $storeIds = array_unique($storeIds);

        $index = array_search(null, $storeIds);
        if ($index) {
            unset($storeIds[$index]);
            $nullCheck = true;
        }

        $storeIds[0] = $storeIds[0] === '' ? 0 : $storeIds[0];
        $selectParams = $select->getPart(Select::FROM);
        $tableNames = array_keys($selectParams);

        if ($nullCheck) {
            $select->where(
                $tableNames[0] . '.store_id IN(?) OR e.store_id IS NULL',
                $storeIds
            );
        } else {
            $select->where(
                $tableNames[0] . '.store_id IN(?)',
                $storeIds
            );
        }

        return $this;
    }

    public function formatDate($date)
    {
        $date = new \DateTime($date);

        return $date->format("Y-m-d H:i:s");
    }
}
