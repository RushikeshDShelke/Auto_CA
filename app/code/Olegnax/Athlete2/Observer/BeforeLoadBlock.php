<?php

namespace Olegnax\Athlete2\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Store\Model\ScopeInterface;
use Olegnax\Athlete2\Block\Product\View;
use Olegnax\Athlete2\Helper\Helper;

class BeforeLoadBlock implements ObserverInterface
{
    const OPTION_CUSTOMTABS_BY_BLOCK = 'athlete2_settings/product/customtabs_by_block';
    const OPTION_CUSTOMTABS_BY_ATTRIBUTE = 'athlete2_settings/product/customtabs_by_attribute';
	const OPTION_CUSTOMTABS_ENABLED = 'athlete2_settings/product/customtabs_enable';

    const CHILD_TEMPLATE = View::class;

    /**
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * BeforeLoadBlock constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Json|null $serializer
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Json $serializer = null
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * @param Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        if (!$this->getConfig(Helper::XML_ENABLED) || !$this->getConfig(static::OPTION_CUSTOMTABS_ENABLED)) {
            return;
        }
        /** @var AbstractBlock $block */
        $block = $observer->getData('block');
        $current_name = $block->getNameInLayout();

        if (in_array($current_name, ['product.info.details'])) {
            $tabs_by_block = $this->getConfig(static::OPTION_CUSTOMTABS_BY_BLOCK);
            $tabs_by_attr = $this->getConfig(static::OPTION_CUSTOMTABS_BY_ATTRIBUTE);
            $tabs_by_block = empty($tabs_by_block) ? false : $this->serializer->unserialize($tabs_by_block);
            $tabs_by_attr = empty($tabs_by_attr) ? false : $this->serializer->unserialize($tabs_by_attr);
            $tabs = [];
            if (is_array($tabs_by_attr)) {
                $tabs = array_merge($tabs, $tabs_by_attr);
            }
            if (is_array($tabs_by_block)) {
                $tabs = array_merge($tabs, $tabs_by_block);
            }
            $index = 40;
            foreach ($tabs as $key => $value) {
                if (!isset($value['sort_order']) || empty($value['sort_order'])) {
                    $tabs[$key]['sort_order'] = $index;
                    $index++;
                }
            }

            uasort($tabs, [$this, 'sorter']);

            foreach ($tabs as $key => $value) {
                /** @var View $_block */
                $_block = $block->getLayout()->createBlock(
                    static::CHILD_TEMPLATE,
                    $current_name . '.custom' . $key,
                    ['data' => $value]
                );
                $_block_name = $_block->getNameInLayout();
                $block->setChild('custom' . $key, $_block);
                $block->getLayout()->addToParentGroup($_block_name, 'detailed_info');
            }
        }
    }

    public function getConfig($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function sorter($first, $next)
    {
        $a = isset($first['sort_order']) ? (int)$first['sort_order'] : 10;
        $b = isset($next['sort_order']) ? (int)$next['sort_order'] : 10;
        if ($a == $b) {
            return 0;
        }
        return ($a < $b) ? -1 : 1;
    }
}
