<?php
/**
 * Dotzot Shipping
 *
 */

namespace Dotzotfront\ShippingTracking\Model\Service;

class Fedex extends \Dotzotfront\ShippingTracking\Model\AbstractService
{
    /** @var string  */
    const TEST_TRACK_NUMBER = '449044304137821';

    /** @var string  */
    const SANDBOX_URL = 'https://gatewaybeta.fedex.com:443/xml';

    /** @var string  */
    const PRODUCTION_URL = 'https://gateway.fedex.com:443/xml';

    /**
     * @param string $inquiryNumber
     * @return mixed
     */
    public function getTrackingInfo($inquiryNumber = '',  $forTest = false, $testData = [])
    {
        if (!$this->getConfig()->enabledFedexApi($this->getCurrentStoreId()) && !$forTest) {
            return [];
        }

        if (!$forTest) {
            $authData['key'] = $this->getConfig()->getKeyFedexApi($this->getCurrentStoreId());
            $authData['password'] = $this->getConfig()->getPasswordFedexApi($this->getCurrentStoreId());
            $authData['account_number'] = $this->getConfig()->getAccountNumberFedexApi($this->getCurrentStoreId());
            $authData['meter_number'] = $this->getConfig()->getMeterNumberFedexApi($this->getCurrentStoreId());
            $url = $this->getUrl();
        } else {
            $authData['key'] = $testData[0];
            $authData['password'] = $testData[1];
            $authData['account_number'] = $testData[2];
            $authData['meter_number'] = $testData[3];
            $url  = ((bool)$testData[4]) ? self::SANDBOX_URL : self::PRODUCTION_URL;
        }

        $preparedParam = $this->prepareParams($authData, $inquiryNumber);
        $header = [
            'Content-Type: text/xml; charset=utf-8',
            'Content-Length: ' . strlen($preparedParam)
        ];

        $params = [
            [
                'option' => CURLOPT_POST,
                'value'  => 1
            ],
            [
                'option' => CURLOPT_RETURNTRANSFER,
                'value'  => 1
            ],
            [
                'option' => CURLOPT_URL,
                'value'  => $url
            ],
            [
                'option' => CURLOPT_HTTPHEADER,
                'value'  => $header
            ],
            [
                'option' => CURLOPT_POST,
                'value'  => 1
            ],
            [
                'option' => CURLOPT_POSTFIELDS,
                'value'  => $preparedParam
            ],
        ];

        $response = $this->call($params);

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
        $result = ['result' => false, 'error' => ''];

        $info = $this->getTrackingInfo(
            self::TEST_TRACK_NUMBER,
            true,
            $data
        );

        if (! empty($info)) {
            $result['result'] = true;
        }

        return $result;
    }

    /**
     * @param $info
     * @return mixed
     */
    public function parseTrackInfo($info)
    {
        $trackDetails = [];
        $result = [];
        $key = 0;

        if (isset($info->TrackDetails[0])) {
            foreach($info->TrackDetails as $item) {
                $trackDetails[] = $item;
            }
        } else {
            $trackDetails = [$info->TrackDetails];
        }
        krsort($trackDetails);

        foreach($trackDetails as $trackDetail) {
            $time = strtotime($trackDetail->ShipTimestamp);
            if ($_events = $trackDetail->Events) {
                $events = [];
                foreach($_events as $item) {
                    $events[] = $item;
                }
                krsort($events);
                foreach($_events as $item) {
                    $time = strtotime($item->Timestamp);
                    $address = $item->Address;

                    if (isset($address)) {
                        $result['info'][$key]['location'] = (($address->City) ? $address->City . ', ' : '') .
                            (($address->StateOrProvinceCode) ? '(' . $address->StateOrProvinceCode . '), ' : '') .
                            (($address->CountryCode) ? $this->getCountryName($address->CountryCode) : '');
                    } else {
                        $result['info'][$key]['location'] = '';
                    }

                    $result['info'][$key]['date'] = $this->timezone->date($time)->format($this->getDateFormat());
                    $result['info'][$key]['time'] = $this->timezone->date($time)->format($this->getTimeFormat());
                }
            } else {
                $address = $trackDetail->DestinationAddress;

                if (isset($address)) {
                    $result['info'][$key]['location'] = (($address->City) ? $address->City . ', ' : '') .
                        (($address->StateOrProvinceCode) ? '('.$address->StateOrProvinceCode .'), ' : '') .
                        (($address->CountryCode) ? $this->getCountryName($address->CountryCode) : '');

                    if (empty($result['info'][$key]['location'])) {
                        continue;
                    }
                } else {
                    $result['info'][$key]['location'] = '';
                }

                $result['info'][$key]['date'] = $this->timezone->date($time)->format($this->getDateFormat());
                $result['info'][$key]['time'] = $this->timezone->date($time)->format($this->getTimeFormat());
            }
            $key++;
        }

        $result['columns'] = [
            __($this->locationColumnName),
            __($this->dateColumnName),
            __($this->timeColumnName)
        ];

        return $result;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        if ($this->getConfig()->getSandboxModeFedexApi($this->getCurrentStoreId())) {
            return self::SANDBOX_URL;
        }

        return self::PRODUCTION_URL;
    }

    /**
     * @param $inquiryNumber
     * @return string
     */
    private function prepareParams($authData, $inquiryNumber)
    {
        $params = '<TrackRequest xmlns="http://fedex.com/ws/track/v3">
            <WebAuthenticationDetail>
                <UserCredential>
                    <Key>' . $authData['key'] . '</Key>
                    <Password>' . $authData['password'] . '</Password>
                </UserCredential>
            </WebAuthenticationDetail>
            <ClientDetail>
                <AccountNumber>'
                    . $authData['account_number'] .
                '</AccountNumber>
                <MeterNumber>'. $authData['meter_number'] . '</MeterNumber>
            </ClientDetail>
            <TransactionDetail>
                <CustomerTransactionId>ActiveShipping</CustomerTransactionId>
            </TransactionDetail>
            <Version>
                <ServiceId>trck</ServiceId>
                <Major>3</Major>
                <Intermediate>0</Intermediate>
                <Minor>0</Minor>
            </Version>
            <PackageIdentifier>
                <Value>' . $inquiryNumber . '</Value>
                <Type>TRACKING_NUMBER_OR_DOORTAG</Type>
            </PackageIdentifier>
            <IncludeDetailedScans>1</IncludeDetailedScans>
        </TrackRequest>';

        return $params;
    }
}
