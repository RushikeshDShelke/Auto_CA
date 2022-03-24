<?php
namespace Pramod\Review\Model;

class Review extends \Magento\Review\Model\Review
{
    public function validate()
    {
        $errors = [];

        if (!\Zend_Validate::is($this->getNickname(), 'NotEmpty')) {
            $errors[] = __('Please enter a Name.');
        }

        if (!\Zend_Validate::is($this->getDetail(), 'NotEmpty')) {
            $errors[] = __('Please enter a Review.');
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }
}
