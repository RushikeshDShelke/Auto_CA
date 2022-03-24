<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Kellton\Ogmeta\Block;

class OgmetaCms extends \Magento\Framework\View\Element\Template
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

    public function getOgHelper()
    {
        return $this->oghelper;
    }
}

