<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Adminhtml\Catalog\Products\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class CreatedAt extends AbstractRenderer
{
    /**
     * Render create date column.
     *
     * @param DataObject $row Row object.
     *
     * @return string
     */
    protected function _getValue(DataObject $row) // @codingStandardsIgnoreLine
    {
        $date = $row->getCreatedAt();
        $datetime = new \DateTime($date);

        return $datetime->format('m/d/Y H:i:s');
    }
}
