<?php

namespace Cminds\Marketplace\Model;

class Pdf extends \Magento\Sales\Model\Order\Pdf\Shipment
{
    const CONFIG_GLOBAL = 'shipping_labels_marketplace';
    const XML_FONTSIZE = 'font_size';
    const XML_PAGEWIDTH = 'page_width';
    const XML_PAGEHEIGHT = 'page_height';
    const XML_TOPMARGIN = 'top_margin';
    const XML_SIDEMARGIN = 'side_margin';
    const XML_NUMBERDOWN = 'number_down';
    const XML_NUMBERACROSS = 'number_across';
    const XML_VERTICALPITCH = 'vertical_pitch';
    const XML_HORIZONTALPITCH = 'horizontal_pitch';
    const XML_BOLDNAME = 'bold_name';
    const XML_STARTFROM = 'start_from';
    const XML_TOPPADDING = 'top_padding';
    const XML_LEFTPADDING = 'left_padding';

    protected $_configSettings = [];
    protected $_currLabel;
    protected $_currRow;
    protected $_currColumn;
    protected $_carrier;
    protected $x;
    protected $orderId;
    protected $_objectManager;
    protected $_order;
    protected $_renderer;

    public function getPdf($shipments = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('shipment');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($shipments as $shipment) {
            if ($shipment->getStoreId()) {
                $this->_localeResolver->emulate($shipment->getStoreId());
                $this->_storeManager->setCurrentStore($shipment->getStoreId());
            }
            $page = $this->newPage();
            $order = $shipment->getOrder();
            $this->insertAddress($page, $shipment->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $shipment,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                )
            );
        }

        $this->_afterGetPdf();
        if ($shipment->getStoreId()) {
            $this->_localeResolver->revert();
        }

        return $pdf;
    }

    protected function insertOrder(&$page, $obj, $putOrderId = true)
    {
        if ($obj instanceof \Magento\Sales\Model\Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof \Magento\Sales\Model\Order\Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }

        $billingAddress = $this->_formatAddress(
            $this->addressRenderer->format(
                $order->getBillingAddress(),
                'pdf'
            )
        );

        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress(
                $this->addressRenderer->format(
                    $order->getShippingAddress(),
                    'pdf'
                )
            );
        }

        $addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            $addressesHeight = max(
                $addressesHeight,
                $this->_calcAddressHeight($shippingAddress)
            );
        }

        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, 10);

        if (!$order->getIsVirtual()) {
            foreach ($shippingAddress as $value) {
                if ($value !== '') {
                    $text = [];
                    $splited = $this->string->split($value, 45, true, true);
                    foreach ($splited as $_value) {
                        $text[] = $_value;
                    }
                    foreach ($text as $part) {
                        $page->drawText(
                            strip_tags(
                                ltrim($part)
                            ),
                            285,
                            $this->y,
                            'UTF-8'
                        );
                        $this->y -= 15;
                    }
                }
            }
        }
    }
}
