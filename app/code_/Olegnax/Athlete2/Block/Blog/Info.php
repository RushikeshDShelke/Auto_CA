<?php

namespace Olegnax\Athlete2\Block\Blog;

use Magento\Store\Model\ScopeInterface;

class Info extends \Magento\Framework\View\Element\Template {

	protected $_coreRegistry;

	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context, \Magento\Framework\Registry $coreRegistry, array $data = []
	) {
		parent::__construct( $context, $data );
		$this->_coreRegistry = $coreRegistry;
	}

	/**
	 * Retrieve post instance
	 *
	 * @return \Magefan\Blog\Model\Post
	 */
	public function getPost() {
		if ( !$this->hasData( 'post' ) ) {
			$this->setData(
			'post', $this->_coreRegistry->registry( 'current_blog_post' )
			);
		}
		return $this->getData( 'post' );
	}

	/**
	 * Block template file
	 * @var string
	 */
	protected $_template = 'Magefan_Blog::post/info.phtml';

	/**
	 * DEPRECATED METHOD!!!!
	 * Retrieve formated posted date
	 * @var string
	 * @return string
	 */
	public function getPostedOn( $format = 'Y-m-d H:i:s' ) {
		return $this->getPost()->getPublishDate( $format );
	}

	/**
	 * Retrieve 1 if display author information is enabled
	 * @return int
	 */
	public function authorEnabled() {
		return (int) $this->_scopeConfig->getValue(
		'mfblog/author/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * Retrieve 1 if author page is enabled
	 * @return int
	 */
	public function authorPageEnabled() {
		return (int) $this->_scopeConfig->getValue(
		'mfblog/author/page_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * Retrieve true if magefan comments are enabled
	 * @return bool
	 */
	public function magefanCommentsEnabled() {
		return $this->_scopeConfig->getValue(
		'mfblog/post_view/comments/type', \Magento\Store\Model\ScopeInterface::SCOPE_STORE
		) == \Magefan\Blog\Model\Config\Source\CommetType::MAGEFAN;
	}

}
