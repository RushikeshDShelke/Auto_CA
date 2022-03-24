<?php

namespace Cminds\Supplierfrontendproductuploader\Model\VendorPanel\Cache;

use Magento\Framework\Cache\Frontend\Decorator\TagScope;
use Magento\Framework\App\Cache\Type\FrontendPool;

/**
 * Cminds Supplierfrontendproductuploader vendor panel cache type.
 *
 * @category Cminds
 * @package  Cminds_Supplierfrontendproductuploader
 * @author   Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Type extends TagScope
{
    /**
     * Cache type code unique among all cache types.
     */
    const TYPE_IDENTIFIER = 'cminds_vendor_panel';

    /**
     * Cache tag used to distinguish the cache type from all other cache.
     */
    const CACHE_TAG = 'CMINDS_VENDOR_PANEL';

    /**
     * Object constructor.
     *
     * @param FrontendPool $cacheFrontendPool
     */
    public function __construct(FrontendPool $cacheFrontendPool)
    {
        parent::__construct(
            $cacheFrontendPool->get(self::TYPE_IDENTIFIER),
            self::CACHE_TAG
        );
    }
}
