<?php

namespace Peakhour\Cdn\Model\System\Config\Source;

use Magento\PageCache\Model\System\Config\Source\Application;

class ApplicationPlugin
{
    public function afterToOptionArray(Application $application, $optionArray)
    {
        return array_merge($optionArray, [['value' => 'peakhour', 'label' => 'Peakhour.io']]);
    }

    public function afterToArray(Application $application, $optionArray)
    {
        $optionArray['peakhour'] ='Peakhour.io';
        return $optionArray;
    }
}
