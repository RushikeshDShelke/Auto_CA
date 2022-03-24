<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\InstagramMin\Model\ResourceModel\IntsPost;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Olegnax\InstagramMin\Model\ResourceModel\IntsPost;

class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'intspost_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Olegnax\InstagramMin\Model\IntsPost::class,
            IntsPost::class
        );
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        foreach ($this->_items as $item) {
            $data = $item->getData('display_url');
            if (!empty($data) && !is_array($data)) {
                $data = explode(',', $data);
                $item->setData('display_url', $data);
            }
        }
        return $this;
    }
}
