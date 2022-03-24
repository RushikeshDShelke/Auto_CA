<?php
namespace Cminds\Supplierfrontendproductuploader\Block\Adminhtml\Sources;

use Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface;

class Status
{
    /**
     * @var SourcesInterface
     */
    protected $source = null;

    protected $statusLabels = [
        SourcesInterface::STATUS_PENDING => 'Pending',
        SourcesInterface::STATUS_APPROVED => 'Approved',
        SourcesInterface::STATUS_REJECTED => 'Rejected'
    ];


    /**
     * @param SourcesInterface $sourceModel
     */
    public function __construct(
        SourcesInterface $source
    ) {
        $this->source = $source;
    }


    /**
     * Get status label by status code
     *
     * @param int $statusCode.
     *
     * @return string
     */
    public function getStatusLabel(int $statusCode)
    {
        return $this->statusLabels[$statusCode];
    }
}
