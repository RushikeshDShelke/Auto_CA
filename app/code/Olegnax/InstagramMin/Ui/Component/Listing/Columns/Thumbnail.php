<?php


namespace Olegnax\InstagramMin\Ui\Component\Listing\Columns;


use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Olegnax\InstagramMin\Model\IntsPostFactory;

class Thumbnail extends Column
{
    /**
     * @var IntsPostFactory
     */
    protected $intsPostFactory;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        IntsPostFactory $intsPostFactory,
        array $components = [],
        array $data = []
    ) {
        $this->intsPostFactory = $intsPostFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $_item = $this->intsPostFactory->create()->load($item['intspost_id']);
                $item['image'] =
                $item['image_src'] =
                $item['image_link'] =
                $item['image_orig_src'] = $_item->getImageUrl();
            }
        }

        return parent::prepareDataSource($dataSource);
    }
}