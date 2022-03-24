<?php


namespace Olegnax\MegaMenu\Model\Cache;

use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Cache\Frontend\Decorator\TagScope;

class MegaMenu extends TagScope
{

    const TYPE_IDENTIFIER = 'ox_megamenu';
    const CACHE_TAG = 'OXMEGAMENU';

    /**
     * @param FrontendPool $cacheFrontendPool
     */
    public function __construct(
        FrontendPool $cacheFrontendPool
    ) {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }
}