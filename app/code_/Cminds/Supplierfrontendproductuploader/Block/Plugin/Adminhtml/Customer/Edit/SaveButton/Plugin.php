<?php

namespace Cminds\Supplierfrontendproductuploader\Block\Plugin\Adminhtml\Customer\Edit\SaveButton;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Block\Adminhtml\Edit\SaveButton;
use Magento\Customer\Controller\Adminhtml\Index\Edit;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\Page;

/**
 * Cminds Marketplace admin customer edit save button block plugin.
 *
 * @category Cminds
 * @package  Cminds_Marketplace
 * @author   Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
class Plugin
{
    /**
     * Request object.
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * Object constructor.
     *
     * @param RequestInterface $request Request object.
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * After get button data plugin.
     *
     * @param SaveButton $subject    Subject object.
     * @param array      $buttonData Button data array.
     *
     * @return array
     */
    public function afterGetButtonData(
        SaveButton $subject,
        array $buttonData
    ) {
        if (!$this->request->getParam('supplier')) {
            return $buttonData;
        }

        $buttonData['label'] = __('Save Supplier');

        return $buttonData;
    }
}
