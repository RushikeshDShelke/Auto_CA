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
namespace Olegnax\BannerSlider\Controller\Adminhtml\Slides;

use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\App\Action {
    const ADMIN_RESOURCE = 'Olegnax_BannerSlider::Slide_Index';
	const IMAGE_FOLDER = 'bannerslider/';

	protected $directory;
	protected $helperImage;

	/**
	 * @param \Magento\Backend\App\Action\Context $context
	 * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
	 */
	public function __construct(
	\Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory,
 \Olegnax\BannerSlider\Helper\Image $helperImage
	) {
		$this->helperImage = $helperImage;
		parent::__construct( $context );
	}

	/**
	 * Save action
	 *
	 * @return \Magento\Framework\Controller\ResultInterface
	 */
	public function execute() {
		$data			 = $this->getRequest()->getPostValue();
		$resultRedirect	 = $this->resultRedirectFactory->create();
		$id				 = $this->getRequest()->getParam( 'slider_id' );
		$model			 = $this->_objectManager->create( \Olegnax\BannerSlider\Model\Slides::class );
		$image_fields	 = [
			'image',
			'imageX2',
		];
		$need_delete	 = [
		];
		foreach ( $image_fields as $fields ) {
			if ( array_key_exists( $fields, $data ) && is_array( $data[ $fields ] ) ) {
				if ( isset( $data[ $fields ][ 'delete' ] ) ) {
					$need_delete[ $fields ] = $data[ $fields ][ 'value' ];
				}
				$data[ $fields ] = $data[ $fields ][ 'value' ];
			} else {
				$data[ $fields ] = '';
			}
		}
		$model->setData( $data );
		$model->save();
		try {
			foreach ( $image_fields as $fields ) {
				if ( array_key_exists( $fields, $need_delete ) ) {
					$this->removeImages( $fields, $model );
				} else {
					$this->saveImages( $fields, $model, $data[ $fields ] );
				}
			}
			$model->save();

			$this->messageManager->addSuccess( __( 'Banner slide has been saved.' ) );
			$this->messageManager->addWarningMessage( __( 'Banner slide has been changed. Please go to Cache Management and Flush Magento Cache.' ) );
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

	protected function imagePath( $subFolder = '', $prefixFolder = '' ) {
		$path = self::IMAGE_FOLDER;
		if ( !empty( $subFolder ) ) {
			$path .= $subFolder . '/';
		}
		if ( !empty( $prefixFolder ) ) {
			$path = $prefixFolder . $path;
		}
		return $path;
	}

	protected function mediaPath() {
		if ( !$this->directory ) {
			$this->directory = $this->_objectManager->get( '\Magento\Framework\Filesystem\DirectoryList' );
		}
		return $this->directory->getRoot() . '/pub/media/';
	}

	protected function saveImages( string $fieldId, $model, $currentValue = '' ) {
		$saveDirectory	 = $this->imagePath( $model->getId(), $this->mediaPath() );
		$result			 = null;
		$filename		 = '';
		try {
			$uploader	 = new \Magento\MediaStorage\Model\File\Uploader(
			$fieldId, $this->_objectManager->create( 'Magento\MediaStorage\Helper\File\Storage\Database' ), $this->_objectManager->create( 'Magento\MediaStorage\Helper\File\Storage' ), $this->_objectManager->create( 'Magento\MediaStorage\Model\File\Validator\NotProtectedExtension' )
			);
			$uploader->setAllowCreateFolders( true );
			$uploader->setAllowRenameFiles( true );
			$uploader->setAllowedExtensions( [
				'jpeg',
				'jpg',
				'png' ] );
			$this->removeImages( $fieldId, $model );
			$result		 = $uploader->save( $saveDirectory );
			$filename	 = $uploader->getUploadedFileName();
		} catch ( \Exception $e ) {
			if ( in_array( $fieldId, [
				'image' ] ) && $e->getCode() != \Magento\Framework\File\Uploader::TMP_NAME_EMPTY ) {
				$this->messageManager->addException( $e, $e->getMessage() );
			}
		}

		if ( $result ) {
			$model->setData( $fieldId, $this->imagePath( $model->getId() ) . $filename );
		} else {
			$model->setData( $fieldId, $currentValue );
		}
	}

	protected function removeImages( string $fieldId, $model, $currentValue = '' ) {
		$image = $model->getData( $fieldId );
		if ( !empty( $image ) ) {
			$this->helperImage->removeResized( $image );
			$path = $this->mediaPath() . $image;
			if ( file_exists( $path ) && is_file( $path ) ) {
				@unlink( $path );
				$model->setData( $fieldId, $currentValue );
				return true;
			}
		}

		return false;
	}

    protected function _isAllowed() {
        return $this->_authorization->isAllowed('Olegnax_BannerSlider::Slide_New') || $this->_authorization->isAllowed('Olegnax_BannerSlider::Slide_Edit');
    }

}
