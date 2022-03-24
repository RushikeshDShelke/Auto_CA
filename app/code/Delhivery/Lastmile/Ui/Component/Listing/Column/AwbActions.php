<?php
/**
 * Delhivery_Lastmile extension
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category  Delhivery
 * @package   Delhivery_Lastmile
 * @copyright Copyright (c) 2018
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Delhivery\Lastmile\Ui\Component\Listing\Column;

class AwbActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Url path  to edit
     * 
     * @var string
     */
    const URL_PATH_EDIT = 'delhivery_lastmile/awb/edit';

    /**
     * Url path  to delete
     * 
     * @var string
     */
    const URL_PATH_DELETE = 'delhivery_lastmile/awb/delete';

    /**
     * Url builder
     * 
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * constructor
     * 
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['awb_id'])) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'awb_id' => $item['awb_id']
                                ]
                            ),
                            'label' => __('Edit Package')
                        ]/*,
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'awb_id' => $item['awb_id']
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete "${ $.$data.awb }"'),
                                'message' => __('Are you sure you wan\'t to delete the Manage&#x20;AWB "${ $.$data.awb }" ?')
                            ]
                        ]*/
                    ];
                }
            }
        }
        return $dataSource;
    }
}
