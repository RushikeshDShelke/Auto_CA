<?php
/**
 * Copyright © Ulmod. All rights reserved.
 * See LICENSE.txt for license details.
 */
 
namespace Ulmod\OrderComment\Model;

use Ulmod\OrderComment\Api\OrderCommentManagementInterface;
use Ulmod\OrderComment\Model\Data\OrderComment;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\CartRepositoryInterface;
use Ulmod\OrderComment\Api\Data\OrderCommentInterface;

class OrderCommentManagement implements OrderCommentManagementInterface
{
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     *
     * @param \CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * @param int $cartId
     * @param OrderCommentInterface $orderComment
     * @return null|string
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function saveOrderComment(
        $cartId,
        OrderCommentInterface $orderComment
    ) {
         $quote = $this->quoteRepository->getActive($cartId);
         
        if (!$quote->getItemsCount()) {
              throw new NoSuchEntityException(
                  __('Cart %1 doesn\'t contain products', $cartId)
              );
        }
        
        $comment = $orderComment->getComment();

        try {
             $quote->setData(OrderComment::COMMENT_FIELD_NAME, strip_tags($comment));
            
             $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
               throw new CouldNotSaveException(
                   __('The order comment could not be saved')
               );
        }

         return $comment;
    }
}
