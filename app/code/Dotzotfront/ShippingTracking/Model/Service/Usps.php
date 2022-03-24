<?php
/**
 * Dotzot Shipping
 *
 */

namespace Dotzotfront\ShippingTracking\Model\Service;

class Usps extends \Dotzotfront\ShippingTracking\Model\AbstractService
{
    /**
     * Test Track Number
     */
    const TEST_TRACK_NUMBER = 'EL303676561US';

    /** @var string  */
    const USPS_URL = 'http://production.shippingapis.com/ShippingAPI.dll?API=TrackV2&XML=';

    /**
     * @param string $inquiryNumber
     * @return mixed
     */
    public function getTrackingInfo($inquiryNumber = '', $forTest = false, $testData = [])
    {
        if (!$this->getConfig()->enabledUspsApi($this->getCurrentStoreId()) && !$forTest) {
            return [];
        }

        if (!$forTest) {
            $authData['user_id'] = $this->getConfig()->getUserIdUspsApi($this->getCurrentStoreId());
        } else {
            $authData['user_id'] = $testData[0];
        }

        $response = file_get_contents(
            self::USPS_URL . $this->prepareParams($authData, $inquiryNumber)
        );

        $result = @simplexml_load_string($response);
        if (!$forTest) {
            $result = $this->parseTrackInfo($result);
        }

        return $result;
    }

    /**
     * @param $data
     * @return array
     */
    public function testService($data)
    {
        $result = ['result' => true, 'error' => ''];

        $info = $this->getTrackingInfo(
            self::TEST_TRACK_NUMBER,
            true,
            $data
        );

        if (isset($info->Description)) {
            $desc = (array)$info->Description;
            $result = [
                'result' => false,
                'error' => $desc[0]
            ];
        }

        return $result;
    }

    /**
     * @param $informations
     * @return array
     */
    public function parseTrackInfo($informations)
    {
        $result = [];
        $key = 0;

        if (isset($informations->TrackInfo->TrackDetail)) {
            foreach($informations->TrackInfo->TrackDetail as $info) {

                $data = explode(',', $info);
                $activity = isset($data[0]) ? $data[0] : '';
                $date = (isset($data[1]) ? $data[1] : '') . (isset($data[2]) ? ', ' . $data[2] : '');

                if (count($data) > 3) {
                    $time = isset($data[3]) ? $data[3] : '';
                    $address = (isset($data[4]) ? $data[4] : '') . ' ' . (isset($data[5]) ? $data[5] : '');
                } else {
                    $time = null;
                    $address = null;
                }

                $result['info'][$key]['location'] = $address;
                $result['info'][$key]['date'] = $date;
                $result['info'][$key]['time'] = $time;
                $result['info'][$key]['status'] = $activity;
                $key++;
            }

            $result['columns'] = [
                __($this->locationColumnName),
                __($this->dateColumnName),
                __($this->timeColumnName),
                __($this->statusColumnName)
            ];
        }

        return $result;
    }

    /**
     * @param $inquiryNumber
     * @return string
     */
    private function prepareParams($authData, $inquiryNumber)
    {
        $params = '<TrackRequest USERID="' . urlencode($authData['user_id'])
            . '"> <TrackID ID="' . $inquiryNumber . '"></TrackID></TrackRequest>';

        return urlencode($params);
    }
}