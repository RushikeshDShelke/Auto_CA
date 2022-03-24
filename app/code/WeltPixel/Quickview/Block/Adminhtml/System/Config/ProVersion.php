<?php

namespace WeltPixel\Quickview\Block\Adminhtml\System\Config;

/**
 * Class ProVersion
 * @package WeltPixel\GoogleTagManager\Block\Adminhtml\System\Config
 */
class ProVersion extends \Magento\Config\Block\System\Config\Form\Field
{
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
       $proOptions = [
           'SEO - Add no follow for the quickview link',
           'SEO - Add no index for quickview',
           'SEO - Add canonical link for quickview',
           'Quickview Types: ',
           '-> Right Side ( Quickview Slides in the right side of the page )',
           '-> Left Side ( Quickview Slides in the left side of the page )',
           'Close on Background Click Option',
       ];

       $messageElements = array_map(
           function($el) {
                return "<p><b>$el</b></p>";
           }, $proOptions
       );

       $message = implode("", $messageElements);

        $html = '<div class="message" style="text-align: left; width: 100%;">' . $message  . '</div>';
        $html .= '<div style="padding: 1em;">Get PRO version of <a href="https://www.weltpixel.com/advance-product-quick-view.html" target="_blank" >Advance Product Quick View and Ajax Cart</a>.</div>';
        $js = '<script type="text/javascript">
            require(["jquery"], function ($) {
                $(document).ready(function () {
                    var $el = $("#row_' . $element->getId() . '");
                    $el.find("td.label").remove();                 
                    $el.find("td.value").css("width", "90%");                 
                });
            });
            </script>';


        return $html . $js;
    }
}