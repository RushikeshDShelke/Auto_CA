<?php
/**
 * Created By : Rohan Hapani
 */
namespace SR\ModifiedCheckout\Plugin\Block\Checkout;
/**
 * Class AttributeMerger
 * @package RH\Helloworld\Plugin\Block\Checkout\AttributeMerger
 */
class AttributeMerger
{
    /**
     * @param \Magento\Checkout\Block\Checkout\AttributeMerger $subject
     * @param $result
     * @return mixed
     */
    public function afterMerge(
        \Magento\Checkout\Block\Checkout\AttributeMerger $subject,
        $result
    )
    {
        $result['firstname']['placeholder'] = __('First Name*');
        $result['lastname']['placeholder'] = __('Last Name');
        $result['street']['children'][0]['placeholder'] = __('Flat No/Plot No/House No/ Floor');
        $result['street']['children'][1]['placeholder'] = __('Street/Area/Locality');
        $result['city']['placeholder'] = __('City');
        $result['postcode']['placeholder'] = __('Pincode*');
        $result['company']['placeholder'] = __('Company');
        $result['telephone']['placeholder'] = __('Mobile Number');
        $result['region_id']['placeholder'] = __('Region Id');
        
        return $result;
    }
}