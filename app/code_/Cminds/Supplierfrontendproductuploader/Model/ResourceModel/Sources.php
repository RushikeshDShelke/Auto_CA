<?php

namespace Cminds\Supplierfrontendproductuploader\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface;

class Sources extends AbstractDb
{

    const SOURCES_TABLE =  'supplierfrontendproductuploader_customer_sources';

    protected function _construct()
    {
        $this->_init(self::SOURCES_TABLE, SourcesInterface::ENTITY_ID);
    }
}
