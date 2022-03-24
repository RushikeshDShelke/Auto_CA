<?php

namespace Cminds\Supplierfrontendproductuploader\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Cminds\Supplierfrontendproductuploader\Block\Adminhtml\Sources\Status as SourceStatusBlock;

class SourceStatus extends Column
{
    /*
     * @var Cminds\Supplierfrontendproductuploader\Block\Adminhtml\Sources\Status
     */
    protected $sourceStatus;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SourceStatusBlock $sourceStatus,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->sourceStatus = $sourceStatus;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$items) {
                $items['status'] = __($this->sourceStatus->getStatusLabel($items['status']));
            }
        }
        return $dataSource;
    }
}
