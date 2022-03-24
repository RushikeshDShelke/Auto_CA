<?php
namespace Cminds\Supplierfrontendproductuploader\Block\Adminhtml\Sources;

use Magento\Backend\Block\Widget\Form\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Cminds\Supplierfrontendproductuploader\Api\Data\SourcesInterface as Sources;

class Edit extends Container
{
    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize blog post edit block.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'source_id';
        $this->_blockGroup = 'Cminds_Supplierfrontendproductuploader';
        $this->_controller = 'adminhtml_sources';

        parent::_construct();

        $this->alterButtons();
    }

    /**
     * Add and remove buttons.
     *
     * @return void
     */
    public function alterButtons()
    {

        $this->buttonList->remove('save');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');

        /** @var Sources $model */
        $model = $this->_coreRegistry->registry('source_item');
        $sourceId = $model->getEntityId();

        // add reject button
        if (Sources::STATUS_PENDING === (int) $model->getStatus()) {
            $this->buttonList->add(
                'rejectsource',
                [
                    'label' => __('Reject Source'),
                    'class' => 'reject',
                    'on_click' => 'deleteConfirm(\'' . __('Please confirm the rejection of this suggested source') . '\', \'' . $this->_getRejectUrl($sourceId) . '\')'
                ],
                -100
            );
        }

        // add approve button
        if (Sources::STATUS_PENDING === (int) $model->getStatus()
            || Sources::STATUS_REJECTED === (int) $model->getStatus()
        ) {
            $this->buttonList->add(
                'approvesource',
                [
                'label' => __('Approve Source'),
                'class' => 'approve',
                'on_click' => 'deleteConfirm(\'' . __('Please confirm the approvment of this suggested source') . '\', \'' . $this->_getApproveUrl($sourceId) . '\')'
                ],
                -50
            );
        }
    }

    /**
     * Check permission for passed action.
     *
     * @param string $resourceId
     * 
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Getter of url for "Approve Source" button.
     *
     * @param int $sourceId
     * 
     * @return string
     */
    protected function _getApproveUrl(int $sourceId)
    {
        return $this->getUrl('supplier/sources/approve', [/*'_current' => true, 'back' => 'edit', 'active_tab' => ''*/ 'id' => $sourceId ]);
    }

    /**
     * Getter of url for "Approve Source" button.
     *
     * @param int $sourceId
     * 
     * @return string
     */
    protected function _getRejectUrl(int $sourceId)
    {
        return $this->getUrl('supplier/sources/reject', [ 'id' => $sourceId ]);
    }
}
