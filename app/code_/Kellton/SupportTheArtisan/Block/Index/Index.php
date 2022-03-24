<?php

namespace Kellton\SupportTheArtisan\Block\Index;


class Index extends \Magento\Framework\View\Element\Template {

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\View\Page\Config $pageConfig,
        array $data = []
    ) {
        $this->pageConfig = $pageConfig;
        parent::__construct($context, $data);
        $this->formKey = $formKey;
    }


    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Support The Artisan | Save The Crafts - Craft Maestros')); 
        $this->pageConfig->setDescription(__('Lend your support to thousands of artisan families across the country.')); // meta description
       // return parent::_prepareLayout();
        return $this;
    }
    public function getFormKey()
    {
         return $this->formKey->getFormKey();
    }

}