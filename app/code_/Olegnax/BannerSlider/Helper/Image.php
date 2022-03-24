<?php

/**
 * Image helper
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\BannerSlider\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Description of Image
 *
 * @author Master
 */
class Image extends \Magento\Framework\App\Helper\AbstractHelper {

	protected $_baseFile;

	/**
	 * @var string
	 */
	private $cachePrefix = 'IMG_INFO';

	/**
	 * @var int
	 */
	protected $_width;

	/**
	 * @var int
	 */
	protected $_height;

	/**
	 * Default quality value (for JPEG images only).
	 *
	 * @var int
	 */
	protected $_quality = 90;

	/**
	 * @var bool
	 */
	protected $_keepAspectRatio = true;

	/**
	 * @var bool
	 */
	protected $_keepFrame = true;

	/**
	 * @var bool
	 */
	protected $_keepTransparency = true;

	/**
	 * @var bool
	 */
	protected $_constrainOnly = true;

	/**
	 * @var bool
	 */
	protected $_cropOnly = false;

	/**
	 * @var int[]
	 */
	protected $_backgroundColor = [ 255, 255, 255 ];

	/**
	 * @var MagentoImage
	 */
	protected $_processor;

	/**
	 * @var string
	 */
	protected $_destinationSubdir;

	/**
	 * Store Manager
	 *
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	private $_storeManager;

	/**
	 * @var \Magento\Framework\Filesystem\Directory\WriteInterface
	 */
	protected $_mediaDirectory;

	public function __construct(
	\Magento\Framework\Filesystem $filesystem, \Magento\Framework\Image\Factory $imageFactory,
 \Magento\Store\Model\StoreManagerInterface $storeManager
	) {
		$this->_mediaDirectory	 = $filesystem->getDirectoryRead( DirectoryList::MEDIA );
		$this->_imageFactory	 = $imageFactory;
		$this->_storeManager	 = $storeManager;
	}

	public function adaptiveResize( $file, $size, $attributes = [] ) {
		$this->init( $file, $attributes );
		list($width, $height) = $this->prepareSize( $size );
		$this->setWidth( $width );
		$this->setHeight( $height );
		if ( $this->_keepAspectRatio && $this->_cropOnly ) {
			$this->crop();
		}
		$this->resize();

		return $this;
	}

	private function prepareSize( $size ) {
		if ( is_array( $size ) && 1 >= count( $size ) ) {
			$size = array_shift( $size );
		}
		if ( !is_array( $size ) ) {
			$size = [ $size, $size ];
		}
		$size	 = array_map( 'intval', $size );
		$size	 = array_map( 'abs', $size );
		return $size;
	}

	public function init( $file, $attributes = [] ) {
		$this->_processor = null;

		$this->setBaseFile( $file );

		$this->attributes = $attributes;
		$this->setImageProperties();
	}

	/**
	 * Retrieve image attribute
	 *
	 * @param string $name
	 * @return string
	 */
	protected function getAttribute( $name ) {
		return $this->attributes[ $name ] ?? null;
	}

	/**
	 * Set image properties
	 *
	 * @return $this
	 */
	protected function setImageProperties() {
		$this->setDestinationSubdir( 'resized' );


		// Set 'keep frame' flag
		$frame = $this->getAttribute( 'frame' );
		if ( !empty( $frame ) ) {
			$this->setKeepFrame( $frame );
		}

		// Set 'constrain only' flag
		$constrain = $this->getAttribute( 'constrain' );
		if ( !empty( $constrain ) ) {
			$this->setConstrainOnly( $constrain );
		}

		// Set 'keep aspect ratio' flag
		$aspectRatio = $this->getAttribute( 'aspect_ratio' );
		if ( !empty( $aspectRatio ) ) {
			$this->setKeepAspectRatio( $aspectRatio );
		}


		// Set 'keep $crop' flag
		$crop = $this->getAttribute( 'crop' );
		if ( !empty( $crop ) ) {
			$this->seCropOnly( $crop );
		}

		// Set 'transparency' flag
		$transparency = $this->getAttribute( 'transparency' );
		if ( !empty( $transparency ) ) {
			$this->setKeepTransparency( $transparency );
		}

		// Set background color
		$background = $this->getAttribute( 'background' );
		if ( !empty( $background ) ) {
			$this->setBackgroundColor( $background );
		}

		return $this;
	}

	/**
	 * @param string $dir
	 * @return $this
	 */
	public function setDestinationSubdir( $dir ) {
		$this->_destinationSubdir = $dir;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDestinationSubdir() {
		return $this->_destinationSubdir;
	}

	/**
	 * @param MagentoImage $processor
	 * @return $this
	 */
	public function setImageProcessor( $processor ) {
		$this->_processor = $processor;
		return $this;
	}

	public function crop() {
		if ( $this->getWidth() === null && $this->getHeight() === null ) {
			return $this;
		}
		if ( $this->_fileExists( $this->getResizedFile() ) ) {
			return $this;
		}

		$_imageSrcWidth	 = $this->getImageProcessor()->getOriginalWidth();
		$_imageSrcHeight = $this->getImageProcessor()->getOriginalHeight();
		$_imageSrcRatio	 = $_imageSrcWidth / $_imageSrcHeight;
		$_ratio			 = $this->_width / $this->_height;

		if ( $_imageSrcRatio != $_ratio ) {
			if ( 1 > $_imageSrcRatio ) {
				$newHeight	 = round( $_imageSrcWidth / $_ratio );
				$deltHeight	 = $_imageSrcHeight - $newHeight;

				$_deltHeight	 = floor( $deltHeight / 2 );
				$__deltHeight	 = $deltHeight % 2;

				$this->getImageProcessor()->crop( $_deltHeight + $__deltHeight, 0, 0, $_deltHeight );
			} elseif ( 1 < $_imageSrcRatio ) {
				$newWidth	 = round( $_imageSrcHeight * $_ratio );
				$deltWidth	 = $_imageSrcWidth - $newWidth;

				$_deltWidth	 = floor( $deltWidth / 2 );
				$__deltWidth = $deltWidth % 2;

				$this->getImageProcessor()->crop( 0, $_deltWidth + $__deltWidth, $_deltWidth, 0 );
			}
		}

		return $this;
	}

	/**
	 * @see \Magento\Framework\Image\Adapter\AbstractAdapter
	 * @return $this
	 */
	public function resize() {
		if ( $this->getWidth() === null && $this->getHeight() === null ) {
			return $this;
		}

		$resizedFile = $this->getResizedFile();
		if ( $resizedFile && $this->_fileExists( $resizedFile ) ) {
			return $this;
		}

		$new_filename = $this->_mediaDirectory->getAbsolutePath( $resizedFile );

		$this->getImageProcessor()->resize( $this->_width, $this->_height );

		$this->getImageProcessor()->save( $new_filename );

		return $this;
	}

	/**
	 * @return MagentoImage
	 */
	public function getImageProcessor() {
		if ( !$this->_processor ) {
			$filename			 = $this->getBaseFile() ? $this->_mediaDirectory->getAbsolutePath( $this->getBaseFile() ) : null;
			$this->_processor	 = $this->_imageFactory->create( $filename );
		}
		$this->_processor->keepAspectRatio( $this->_keepAspectRatio );
		$this->_processor->keepFrame( $this->_keepFrame );
		$this->_processor->keepTransparency( $this->_keepTransparency );
		$this->_processor->constrainOnly( $this->_constrainOnly );
		$this->_processor->backgroundColor( $this->_backgroundColor );
		$this->_processor->quality( $this->_quality );
		return $this->_processor;
	}

	/**
	 * @return string
	 */
	public function getBaseFile() {
		return $this->_baseFile;
	}

	/**
	 * Set filenames for base file and new file
	 *
	 * @param string $file
	 * @return $this
	 * @throws \Exception
	 */
	public function setBaseFile( $file ) {
		if ( $this->_fileExists( $file ) ) {
			$this->_baseFile = $file;
		}

		return $this;
	}

	private function getResizedFile() {
		$new_filename = pathinfo( $this->getBaseFile() );
		if ( is_array( $new_filename ) && !empty( $new_filename ) ) {
			$new_filename[ 'filename' ] = implode( '_', [ $new_filename[ 'filename' ], $this->getWidth(), $this->getHeight() ] ) . '.' . $new_filename[ 'extension' ];
			return implode( '/', [ $this->getDestinationSubdir(), $new_filename[ 'dirname' ], $new_filename[ 'filename' ] ] );
		}
	}

	/**
	 * First check this file on FS
	 * If it doesn't exist - try to download it from DB
	 *
	 * @param string $filename
	 * @return bool
	 */
	protected function _fileExists( $filename ) {
		return file_exists( $this->_mediaDirectory->getAbsolutePath( $filename ) );
	}

	/**
	 * @param int $width
	 * @return $this
	 */
	public function setWidth( $width ) {
		$this->_width = $width;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getWidth() {
		return $this->_width;
	}

	/**
	 * @param int $height
	 * @return $this
	 */
	public function setHeight( $height ) {
		$this->_height = $height;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getHeight() {
		return $this->_height;
	}

	/**
	 * Set image quality, values in percentage from 0 to 100
	 *
	 * @param int $quality
	 * @return $this
	 */
	public function setQuality( $quality ) {
		$this->_quality = $quality;
		return $this;
	}

	/**
	 * Get image quality
	 *
	 * @return int
	 */
	public function getQuality() {
		return $this->_quality;
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->_storeManager->getStore()->getBaseUrl( \Magento\Framework\UrlInterface::URL_TYPE_MEDIA ) . $this->getResizedFile();
	}

	/**
	 * @param bool $keep
	 * @return $this
	 */
	public function setKeepAspectRatio( $keep ) {
		$this->_keepAspectRatio = $keep && $keep !== 'false';
		return $this;
	}

	/**
	 * @param bool $keep
	 * @return $this
	 */
	public function setKeepFrame( $keep ) {
		$this->_keepFrame = $keep && $keep !== 'false';
		return $this;
	}

	/**
	 * @param bool $keep
	 * @return $this
	 */
	public function setKeepTransparency( $keep ) {
		$this->_keepTransparency = $keep && $keep !== 'false';
		return $this;
	}

	/**
	 * @param bool $flag
	 * @return $this
	 */
	public function setConstrainOnly( $flag ) {
		$this->_constrainOnly = $flag && $flag !== 'false';
		return $this;
	}

	/**
	 * @param bool $flag
	 * @return $this
	 */
	public function seCropOnly( $flag ) {
		$this->_cropOnly = $flag && $flag !== 'false';
		return $this;
	}

	/**
	 * @param int[] $rgbArray
	 * @return $this
	 */
	public function setBackgroundColor( array $rgbArray ) {
		$this->_backgroundColor = $rgbArray;
		return $this;
	}

	/**
	 * @param string $size
	 * @return $this
	 */
	public function setSize( $size ) {
		// determine width and height from string
		$width	 = $height	 = 0;

		if ( is_numeric( $size ) ) {
			$width	 = $height	 = $size;
		} elseif ( is_array( $size ) ) {
			if ( 1 >= count( $size ) ) {
				list($width, $height) = $size;
			} else {
				$width	 = $height	 = array_shift( $size );
			}
		} elseif ( is_string( $size ) ) {
			list($width, $height) = explode( 'x', strtolower( $size ), 2 );
		}




		foreach ( [ 'width', 'height' ] as $wh ) {
			${$wh} = (int) ${$wh};
			if ( empty( ${$wh} ) ) {
				${$wh} = null;
			}
		}

		// set sizes
		$this->setWidth( $width )->setHeight( $height );

		return $this;
	}

	/**
	 * Get image size
	 *
	 * @param string $imagePath
	 * @return array
	 */
	private function getImageSize( $imagePath ) {
		$imageSize = getimagesize( $imagePath );
		return $imageSize;
	}

	public function removeResized( $file ) {
		if ( !empty( $file ) ) {
			$this->init( $file );
		}
		$file = $this->getBaseFile();

		if ( !empty( $file ) ) {
			$new_filename				 = pathinfo( $file );
			$new_filename[ 'filename' ]	 = implode( '_', [ $new_filename[ 'filename' ], '*' ] ) . '.' . $new_filename[ 'extension' ];
			$pattern					 = implode( '/', [ $this->getDestinationSubdir(), $new_filename[ 'dirname' ], $new_filename[ 'filename' ] ] );
			$pattern					 = $this->_mediaDirectory->getAbsolutePath( $pattern );
			$files						 = (array) @glob( $pattern );
			if ( !empty( $files ) ) {
				foreach ( $files as $_file ) {
					if ( file_exists( $_file ) ) {
						@unlink( $_file );
					}
				}
			}
		}
	}

}
