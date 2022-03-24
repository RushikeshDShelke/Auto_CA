<?php

/**
 * Olegnax BannerSlider
 * 
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Olegnax.com license that is
 * available through the world-wide-web at this URL:
 * https://www.olegnax.com/license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Olegnax
 * @package     Olegnax_BannerSlider
 * @copyright   Copyright (c) 2021 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\BannerSlider\Controller\Adminhtml\Group;

use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\App\Action {

	/**
	 * @param \Magento\Backend\App\Action\Context $context
	 * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
	 */
	public function __construct(
	\Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory
	) {
		parent::__construct( $context );
	}

    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Olegnax_BannerSlider::Group_New') || $this->_authorization->isAllowed('Olegnax_BannerSlider::Group_Edit');
    }

	/**
	 * Save action
	 *
	 * @return \Magento\Framework\Controller\ResultInterface
	 */
	public function execute() {
		$data			 = $this->getRequest()->getPostValue();
		$resultRedirect	 = $this->resultRedirectFactory->create();
		$id				 = $this->getRequest()->getParam( 'group_id' );
		$model			 = $this->_objectManager->create( \Olegnax\BannerSlider\Model\Group::class );
		$identifier		 = $data[ 'identifier' ];
		if ( empty( $identifier ) ) {
			$identifier = $data[ 'group_name' ];
		}
		$_identifier = $identifier	 = str_replace( ' ', '-', strtolower( trim( $identifier ) ) );
		$suffix		 = '';
		$index		 = 0;
		do {
			$identifier	 = $_identifier . $suffix;
			$index++;
			$suffix		 = '-' . $index;
		} while ( !$this->isUniqueIdentifier( $identifier, $id ) );
		$data[ 'identifier' ] = $identifier;

		$model->setData( $data );

		try {
			$model->save();

			$this->messageManager->addSuccess( __( 'Banner group has been saved.' ) );
			$this->messageManager->addWarningMessage( __( 'Banner group has been changed. Please go to Cache Management and Flush Magento Cache.' ) );
			if ( $this->getRequest()->getParam( 'back' ) ) {
				return $resultRedirect->setPath( '*/*/edit', [
					'id'		 => $model->getId(),
					'_current'	 => true ] );
			}
			$this->_objectManager->get( 'Magento\Backend\Model\Session' )->setFormData( false );
			return $resultRedirect->setPath( '*/*/' );
		} catch ( \Exception $e ) {
			throw new \Magento\Framework\Exception\LocalizedException( __( '%1', $e->getMessage() ) );
			$this->messageManager->addException( $e, __( 'Something went wrong.' ) );
		}
		$this->_getSession()->setFormData( $data );
		return $resultRedirect->setPath( '*/*/edit', [
			'id' => $id ] );
	}

	protected function isUniqueIdentifier( $identifier, $id = 0 ) {
		if ( empty( $identifier ) ) {
			return false;
		}
		$itemCollection = \Magento\Framework\App\ObjectManager::getInstance()->get( '\Olegnax\BannerSlider\Model\ResourceModel\Group\CollectionFactory' )->create()
		->addFieldToFilter( 'identifier', $identifier )
		->addFieldToFilter( 'group_id', [ 'neq' => $id ] );
		return count( $itemCollection ) == 0;
	}

}
