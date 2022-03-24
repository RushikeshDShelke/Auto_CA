<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\InstagramMin\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class IntsPost extends AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('olegnax_instagrammin_intspost', 'intspost_id');
    }

    /**
     * @param AbstractModel $object
     * @return IntsPost
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $data = $object->getData('display_url');
        if (is_array($data)) {
            $data = implode(',', $data);
            $object->setData('display_url', $data);
        }
        return parent::_beforeSave($object);
    }

    /**
     * @param AbstractModel $object
     * @return IntsPost
     */
    protected function _afterLoad(AbstractModel $object)
    {
        $data = $object->getData('display_url');
        if (!empty($data) && !is_array($data)) {
            $data = explode(',', $data);
            $object->setData('display_url', $data);
        }
        return parent::_afterLoad($object);
    }
}

