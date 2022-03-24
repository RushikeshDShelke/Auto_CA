<?php

namespace Olegnax\Athlete2\Block\Adminhtml\Import;

class Index extends \Olegnax\Athlete2\Block\Adminhtml\Import {

	const DEMO_IMPORT_PATH = '*/*/import';

	public function getDemo() {
		$demoDir = $this->getAbsolutePath( \Olegnax\Athlete2\Controller\Adminhtml\Import\Import::DEMO_DIR );
		$demos	 = array();

		if ( is_dir( $demoDir ) ) {
			$paths = glob( $demoDir . '/*', GLOB_ONLYDIR );
			foreach ( $paths as $path ) {
				if ( !is_dir( $path ) ) {
					continue;
				}
				$variations = [];

				$demoId	 = basename( $path );
				$vpaths	 = glob( $path . '/*.xml' );
				foreach ( $vpaths as $vpath ) {
					$vdemoId = pathinfo( $vpath, PATHINFO_FILENAME );

					$variations[] = $vdemoId;
				}
				if ( !empty( $variations ) ) {
					$demos[ $demoId ] = [
						'name'		 => $this->convertString( $demoId ),
						'variations' => $variations,
					];
				}
			}
		}

		return $demos;
	}

	public function getDemoImage( $demoId ) {
		$demoId = str_replace( DIRECTORY_SEPARATOR, '/', $demoId );
		return $this->_urlBuilder->getBaseUrl( [ '_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA ] ) . 'athlete2/Demos/' . $demoId . '.jpg';
	}

	public function getDemoId( $demoId ) {
		return str_replace( DIRECTORY_SEPARATOR, '-', $demoId );
	}

	public function actionImport( $demoId, $vdemoId, array $subArguments = [] ) {
		$arguments = [ 'subdir' => $demoId, 'demo' => $vdemoId ];
		if ( $storeId = $this->getRequest()->getParam( 'store' ) ) {
			$arguments[ 'store' ] = $storeId;
		} elseif ( $websiteId = $this->getRequest()->getParam( 'website' ) ) {
			$arguments[ 'website' ] = $websiteId;
		}
		if ( is_array( $subArguments ) ) {
			$arguments = array_merge( $arguments, $subArguments );
		}

		$url = $this->getUrl( self::DEMO_IMPORT_PATH, $arguments );

		return $url;
	}

	public function convertString( $demoId ) {
		return ucwords( strtolower( str_replace( [ '-', '_' ], ' ', $demoId ) ) );
	}

}
