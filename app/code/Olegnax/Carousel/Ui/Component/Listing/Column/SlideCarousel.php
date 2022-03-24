<?php


namespace Olegnax\Carousel\Ui\Component\Listing\Column;


use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Olegnax\Carousel\Model\ResourceModel\Carousel\Collection;
use Olegnax\Carousel\Model\Slide\Source\Carousel;

class SlideCarousel extends Column
{

    /**
     * @var Collection
     */
    protected $collection;
    /**
     * @var array
     */
    protected $options;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Carousel $optionsCarousel,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->options = $optionsCarousel->toArray();
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item['carousel'] = $this->getCarousel($item['carousel']);
            }
        }

        return parent::prepareDataSource($dataSource);
    }

    protected function getCarousel($identifier)
    {
        return array_key_exists($identifier, $this->options) ? $this->options[$identifier] : '';
    }

}