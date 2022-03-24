<?php

namespace Cminds\Marketplace\Block\Order\View;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutFactory;

class AdditionalTabs extends Template
{
    /** @var array $tabs */
    protected $tabs = [];

    /** @var LayoutFactory */
    protected $layoutFactory;


    public function __construct(
        Template\Context $context,
        LayoutFactory $layoutFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * Get additional tabs
     *
     * @return array
     */
    public function getAdditionalTabs()
    {
        return $this->getData('tabs') ?? [];
    }

    public function canShowTab($tab)
    {
        return $tab['block']->setOrder($this->getOrder())->canShow();
    }

    /**
     * Get tab content
     *
     * @param array $tab
     * @return mixed
     */
    public function getTabContent($tab)
    {
        return $tab['block']
            ->setTemplate($tab['template'])
            ->setOrder($this->getOrder())
            ->toHtml();
    }

}
