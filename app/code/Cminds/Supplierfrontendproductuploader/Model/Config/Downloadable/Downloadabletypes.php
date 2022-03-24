<?php

namespace Cminds\Supplierfrontendproductuploader\Model\Config\Downloadable;

class Downloadabletypes
{
    public function toOptionArray()
    {
        $uploader = [
            ['label' => 'jpg', 'value' => 'jpg'],
            ['label' => 'jpeg', 'value' => 'jpeg'],
            ['label' => 'pdf', 'value' => 'pdf'],
            ['label' => 'png', 'value' => 'png'],
            ['label' => 'gif', 'value' => 'gif'],
            ['label' => 'csv', 'value' => 'csv'],
            ['label' => 'zip', 'value' => 'zip'],
        ];

        return $uploader;
    }
}
