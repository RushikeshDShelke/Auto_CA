<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Kellton\Ogmeta\Block;

class OgmetaCatProd extends \Magento\Framework\View\Element\Template
{

    protected $oghelper;
    
    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Kellton\Ogmeta\Helper\Data $oghelper,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->oghelper = $oghelper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    // public function getOgmetaForProduct()
    // {
    //     //Your block code
    //     return __('Hello Developer! This how to get the storename: %1 and this is the way to build a url: %2', $this->_storeManager->getStore()->getName(), $this->getUrl('contacts'));
    // }

    public function getOgHelper()
    {
        return $this->oghelper;
    }
}

