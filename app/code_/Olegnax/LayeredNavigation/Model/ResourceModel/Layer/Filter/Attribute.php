<?php
/**
 * @author      Olegnax
 * @package     Olegnax_LayeredNavigation
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * @license     Proprietary License https://olegnax.com/license/
 */

namespace Olegnax\LayeredNavigation\Model\ResourceModel\Layer\Filter;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Catalog\Model\Layer\Search;
use Magento\Framework\DB\Select;
use Zend_Db_Expr;

class Attribute extends \Magento\Catalog\Model\ResourceModel\Layer\Filter\Attribute
{

    public function getCount(FilterInterface $filter)
    {
        $layer = $filter->getLayer();
        if ($layer instanceof Search) {
            $collectionSelect = $layer->getProductCollection()->getSelect();
        } else {
            $collectionSelect = $layer->getCurrentCategory()->getProductCollection()->getSelect();
        }

        $select = clone $collectionSelect;
        $select->reset(Select::COLUMNS);
        $select->reset(Select::ORDER);
        $select->reset(Select::LIMIT_COUNT);
        $select->reset(Select::LIMIT_OFFSET);

        $connection = $this->getConnection();
        $attribute = $filter->getAttributeModel();
        $tableAlias = sprintf('%s_idx', $attribute->getAttributeCode());
        $conditions = [
            "{$tableAlias}.entity_id = e.entity_id",
            $connection->quoteInto("{$tableAlias}.attribute_id = ?", $attribute->getAttributeId()),
            $connection->quoteInto("{$tableAlias}.store_id = ?", $filter->getStoreId()),
        ];

        $select->join(
            [$tableAlias => $this->getMainTable()], join(' AND ', $conditions),
            ['value', 'count' => new Zend_Db_Expr("COUNT({$tableAlias}.entity_id)")]
        )->group(
            "{$tableAlias}.value"
        );

        return $connection->fetchPairs($select);
    }

}
