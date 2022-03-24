<?php
namespace Magecomp\Ordercomment\Model;

use Magecomp\Ordercomment\Helper\Data\Ordercomment;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;
use Magecomp\Ordercomment\Api\Ordercommentmanagementinterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magecomp\Ordercomment\Api\Data\Ordercommentinterface;

class Ordercommentmanagement implements Ordercommentmanagementinterface
{
    protected $quoteRepository;
    protected $scopeConfig;
    protected $helperOrderComment;

    public function __construct(CartRepositoryInterface $quoteRepository, ScopeConfigInterface $scopeConfig, Ordercomment $helperOrderComment)
    {
        $this->quoteRepository = $quoteRepository;
        $this->scopeConfig = $scopeConfig;
        $this->helperOrderComment = $helperOrderComment;
    }

    public function saveOrdercomment($cartId, Ordercommentinterface $orderComment)
    {
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }
        $comment = $orderComment->getComment($quote->getStoreId());
        $this->validateComment($comment, $quote->getStoreId());

        try {
            $quote->setData(Ordercomment::COMMENT_FIELD_NAME, strip_tags($comment));
            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('The order comment could not be saved'));
        }
        return $comment;
    }

    protected function validateComment($comment, $storeId)
    {
        $maxLength = $this->helperOrderComment->getMaxLength($storeId);
        if ($maxLength && (mb_strlen($comment) > $maxLength)) {
            throw new ValidatorException(__('Comment is too long'));
        }
    }
}

