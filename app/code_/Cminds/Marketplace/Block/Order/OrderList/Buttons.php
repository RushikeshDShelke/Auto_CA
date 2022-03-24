<?php

namespace Cminds\Marketplace\Block\Order\OrderList;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Buttons extends Template
{
    /** @var array $buttons */
    protected $buttons;

    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get additional buttons for orders grid row
     *
     * @return array|null
     */
    public function getButtons()
    {
        return $this->data['button'] ?? [];
    }
}
