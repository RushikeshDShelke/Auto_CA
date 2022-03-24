<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Olegnax\InstagramMin\Block\Widget;

use Magento\Customer\Model\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Olegnax\InstagramMin\Helper\Helper;
use Olegnax\InstagramMin\Model\ResourceModel\IntsPost\CollectionFactory;

class Instagram extends Template implements BlockInterface
{
    const BASE_TEMPLATE = 'Olegnax_InstagramMin::widget/instagram_list.phtml';

    protected $_template = 'Olegnax_InstagramMin::widget/instagram_list.phtml';
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;
    /**
     * @var Json
     */
    protected $json;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * Carousel constructor.
     * @param Template\Context $context
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CollectionFactory $collectionFactory,
        \Magento\Framework\App\Http\Context $httpContext,
        Helper $helper,
        Json $json,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->httpContext = $httpContext;
        $this->helper = $helper;
        $this->json = $json;
        parent::__construct($context, $data);
    }

    /**
     * @param array $newval
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCacheKeyInfo($newval = [])
    {
        return array_merge([
            'OLEGNAX_INSTAGRAM_WIDGET',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(Context::CONTEXT_GROUP),
            $this->json->serialize($this->getData()),
        ], parent::getCacheKeyInfo(), $newval);
    }

    public function getPosts()
    {
        if (!$this->helper->isEnabled()) {
            return null;
        }
        $pageSize = (int)$this->getData('images_count');
        if (1 > $pageSize) {
            $pageSize = 1;
        }

        return $this->collectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('owner', $this->helper->getProfileName())
            ->addFieldToFilter('is_active', 1)
            ->setOrder("taken_at_timestamp")
            ->setPageSize($pageSize);
    }

    public function getUnderlineNameInLayout()
    {
        $name = $this->getNameInLayout();
        $name = preg_replace('/[^a-zA-Z0-9_]/i', '_', $name);
        $name .= substr(md5(microtime()), -5);

        return $name;
    }

    public function isLazyLoadEnabled()
    {
        return $this->helper->isLazyLoadEnabled() &&
            'noexclude' == $this->getData('lazy_load');
    }

    protected function _construct()
    {
        $this->addData([
            'cache_lifetime' => 86400,
        ]);
        if (!$this->hasData('template') && !$this->getTemplate()) {
            $this->setTemplate(static::BASE_TEMPLATE);
        }

        parent::_construct();
    }
}

