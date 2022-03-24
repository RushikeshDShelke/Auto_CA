<?php

namespace Cminds\Marketplace\Controller\Adminhtml\Suppliers;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Cminds\Marketplace\Model\Rating;
use Magento\Framework\Exception\LocalizedException ;

class RemoveRate extends Action
{
    /**
     * @var Rating
     */
    protected $rating;

    public function __construct(
        Context $context,
        Rating $rating
    ) {
        parent::__construct($context);

        $this->rating = $rating;
    }

    public function execute()
    {
        $rateId = $this->getRequest()->getParam('rateId', false);
        if ($rateId) {
            $rating = $this->rating->load($rateId);

            if (!$rating->getId()) {
                $this->messageManager->addError(
                    __('This rate no longer exists.')
                );
            }

            try {
                $rating->delete();
            } catch (LocalizedException $e) {
                $this->messageManager->addError(
                    __('Can not cancel this rate.')
                );
            }
        }

        $this->messageManager->addSuccess(
            __('Rate was canceled.')
        );

        return $this->_redirect(
            $this->_redirect->getRefererUrl()
        );
    }
}
