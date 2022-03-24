<?php

namespace Cminds\Supplierfrontendproductuploader\Observer\Adminhtml;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AttributeSave implements ObserverInterface
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
     * Execute observer logic.
     *
     * @param Observer $observer Observer object.
     *
     * @return AttributeSave
     */
    public function execute(Observer $observer)
    {
        $postData = $this->request->getParams();

        if (empty($postData)) {
            return $this;
        }

        if (isset($postData['data'])) {
            $data = json_decode($postData['data'], true);
        } else {
            $data = $postData;
        }

        if (!isset($data['available_for_supplier'])) {
            return $this;
        }

        $attributeSet = $observer->getObject();
        $attributeSet->setData(
            'available_for_supplier',
            $data['available_for_supplier']
        );

        return $this;
    }
}
