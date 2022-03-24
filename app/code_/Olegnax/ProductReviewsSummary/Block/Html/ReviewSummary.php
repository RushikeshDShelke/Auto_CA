<?php

/**
 * Olegnax ProductReviewsSummary
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
 * @package     Olegnax_ProductReviewsSummary
 * @copyright   Copyright (c) 2021 Olegnax (http://www.olegnax.com/)
 * @license     https://www.olegnax.com/license
 */

namespace Olegnax\ProductReviewsSummary\Block\Html;

class ReviewSummary extends \Magento\Review\Block\Product\View {

	protected function _construct() {
		$_avg_arr = [];
		$rating_votes = [];
		$_items = $this->getReviewsItems();
		if (count($_items)) {
			foreach ($_items as $_review) {
				$_rating_votes = $_review->getRatingVotes();
				if (count($_rating_votes)) {
					$_avg_arr_review = [];
					foreach ($_rating_votes as $_vote) {
						$_avg_arr_review[] = $_vote->getValue();

						$_rating_id = $_vote->getRatingId();
						if (array_key_exists($_rating_id, $rating_votes)) {
							foreach (['percent', 'value'] as $field) {
								$value = $rating_votes[$_rating_id]->getData($field);
								$value[] = $_vote->getData($field);
								$rating_votes[$_rating_id]->setData($field, $value);
							}
						} else {
							foreach (['percent', 'value'] as $field) {
								$_vote->setData($field, [$_vote->getData($field)]);
							}
							$rating_votes[$_rating_id] = $_vote;
						}
					}
					$_avg_arr[] = $this->getAverage($_avg_arr_review);
				}
			}
		}
		$this->setRatingVotes($rating_votes);
		$this->setNumberRatings($_avg_arr);
		$this->setData('average_rating', round($this->getAverage($_avg_arr), 1));
	}

	public function getPercent() {
		return round($this->getData('average_rating') * 100 / 5);
	}

	private function getAverage(array $array) {
		$count = count($array);
		if (!$count) {
			return 0;
		}
		return array_sum($array) / $count;
	}

	private function setNumberRatings(array $array) {
		$array = array_map('round', $array);
		$array = array_map('intval', $array);
		rsort($array, SORT_NUMERIC);
		$_array = [
			5 => 0,
			4 => 0,
			3 => 0,
			2 => 0,
			1 => 0,
		];
		foreach ($array as $vote) {
			$_array[$vote] ++;
		}

		$this->setData('number_of_ratings', $_array);
	}

	private function setRatingVotes(array $rating_votes) {
		foreach ($rating_votes as $_rating_id => $_vote) {
			foreach (['percent', 'value'] as $field) {
				$_vote->setData($field, $this->getAverage($_vote->getData($field)));
			}
		}

		$this->setData('rating_votes', $rating_votes);
	}

	public function getReviewCount() {
		return $this->getReviewsCollection()->getSize();
	}

	private function getReviewsItems() {
		$this->getReviewsCollection()->load()->addRateVotes();
		$_items = $this->getReviewsCollection()->getItems();

		return $_items;
	}

}
