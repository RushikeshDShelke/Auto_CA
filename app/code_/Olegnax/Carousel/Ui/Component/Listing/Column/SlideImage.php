<?php


namespace Olegnax\Carousel\Ui\Component\Listing\Column;


use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Olegnax\Carousel\Model\SlideFactory;

class SlideImage extends Column
{
    /**
     * @var SlideFactory
     */
    protected $slideFactory;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SlideFactory $slideFactory,
        array $components = [],
        array $data = []
    ) {
        $this->slideFactory = $slideFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $_item = $this->slideFactory->create()->load($item['slide_id']);
                if ($_item->hasImage()) {
                    $item['image'] =
                    $item['image_link'] =
                    $item['image_orig_src'] =
                    $item['image_src'] = $_item->getImageUrl();
                }
            }
        }

        return parent::prepareDataSource($dataSource);
    }
}