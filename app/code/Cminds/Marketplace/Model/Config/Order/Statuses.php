<?php

namespace Cminds\Marketplace\Model\Config\Order;

use Magento\Sales\Model\Order\Status;

class Statuses
{
    protected $status;

    public function __construct(
        Status $status
    ) {
        $this->status = $status;
    }

    public function toOptionArray()
    {
        $statuses = $this->status->getResourceCollection()->getData();
        $canSee = [];
        foreach ($statuses as $status) {
            $canSee[] = [
                'value' => $status['status'],
                'label' => $status['label'],
            ];
        }

        return $canSee;
    }
}
