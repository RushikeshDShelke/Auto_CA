<?php

namespace Kellton\Connectus\Block\Index;


class Index extends \Magento\Framework\View\Element\Template {

    public function __construct(\Magento\Catalog\Block\Product\Context $context, array $data = []) {

        parent::__construct($context, $data);

    }


    protected function _prepareLayout()
    {
    	$breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
		$breadcrumbs->addCrumb('home', array('label'=>'Home', 'title'=>'Home', 'link'=>$this->getBaseUrl()));
		$breadcrumbs->addCrumb('connectus', array('label'=>'Connect with us', 'title'=>'Connect with us', 'link'=>"#"));
        return parent::_prepareLayout();
    }

}