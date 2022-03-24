<?php


namespace Olegnax\Carousel\Ui\Component\Listing\Column;

use Magento\Store\Ui\Component\Listing\Column\Store;

class SlideStore extends Store
{
    protected function prepareItem(array $item)
    {
        $item[$this->storeKey] = empty($item[$this->storeKey]) ? '0' : $item[$this->storeKey];

        if (!is_array($item[$this->storeKey])) {
            $item[$this->storeKey] = explode(',', $item[$this->storeKey]);
        }

        return parent::prepareItem($item);
    }

}