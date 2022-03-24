<?php
/**
 * @author Evince Team
 * @copyright Copyright (c) 2018 Evince (http://evincemage.com/)
 */

namespace DotTrack\Trackorder\Block;

class View extends \Magento\Framework\View\Element\Template
{

    protected $_template = 'order.phtml';

    protected $_urlBuilder;

    /**
     * View constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\View\Element\Html\Link $urlBuilder,
        array $data = []
    ) {
       $this->_urlBuilder = $urlBuilder;
        parent::__construct($context, $data);
    }
    
    public function getPostActionUrl()
    {
        return $this->_urlBuilder->getUrl('trackorder/order/shipment');
    }
}
