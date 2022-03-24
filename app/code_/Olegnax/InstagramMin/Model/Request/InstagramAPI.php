<?php


namespace Olegnax\InstagramMin\Model\Request;


class InstagramAPI
{

    const BASE_URL = 'https://www.instagram.com/';
    private $profile_name;

    public function __construct($profile)
    {
        if (is_string($profile)) {
            $this->profile_name = $profile;
        }
    }

    public function getUser($profile = '')
    {
        if (empty($profile)) {
            $profile = $this->profile_name;
        }
        $user = $this->_parseBase($this->_makeBase(self::BASE_URL . $profile . '/'));
        if (!empty($user)) {
            foreach ([
                         'edge_felix_video_timeline',
                         'edge_owner_to_timeline_media',
                         'edge_saved_media',
                         'edge_media_collections',
                     ] as $item) {
                if (isset($user[$item])) {
                    unset($user[$item]);
                }
            }
            return $user;
        }
        return null;
    }

    protected function _parseBase($data)
    {
        if (!empty($data)) {
            $data = json_decode($data, true);
            if (is_array($data) && isset($data['entry_data'])) {
                if (isset($data['entry_data']['ProfilePage'])) {
                    return $data['entry_data']['ProfilePage'][0]['graphql']['user'];
                } elseif (isset($data['entry_data']['HttpErrorPage'])) {
                    throw new InstagramException(__('Profile name is incorrect!'));
                } elseif (isset($data['entry_data']['LoginAndSignupPage'])) {
                    throw new InstagramException(__('Too frequent requests! And awaiting a captcha check!'));
                } else {
                    throw new InstagramException(__('Too frequent requests!'));
                }
            } else {
                throw new InstagramException(__('Failed to convert data!'));
            }
        } else {
            throw new InstagramException(__('No data was received!'));
        }
        return null;
    }

    protected function _makeBase($url, $params = null, $method = 'GET')
    {
        $paramString = null;
        if (isset($params) && is_array($params)) {
            $paramString = '&' . http_build_query($params);
        }
        $apiCall = $url . (('GET' === $method) ? $paramString : null);
        $curl_data = [
            CURLOPT_URL => $apiCall,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_REFERER => static::BASE_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST =>'GET',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 90,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_COOKIEFILE => BP . '/var/tmp/ox_instagram.cookies',
            CURLOPT_COOKIEJAR => BP . '/var/tmp/ox_instagram.cookies',
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.61 Safari/537.36',
        ];
        if ($method == 'POST') {
            $curl_data[CURLOPT_POST] = count($params);
            $curl_data[CURLOPT_POSTFIELDS] = ltrim($paramString, '&');
        }

        $ch = curl_init();
        curl_setopt_array($ch, $curl_data);
        $data = curl_exec($ch);
        if (!empty($data)) {
            $_data = explode('_sharedData = ', $data);
            if (array_key_exists(1, $_data)) {
                $_data = $_data[1];
                $_data = explode('</script>', $_data);
                if (array_key_exists(0, $_data)) {
                    $_data = $_data[0];
                    return preg_replace('/\;$/', '', $_data);
                } else {
                    throw new InstagramException(__("Received incorrect data from Instagram!"));
                }
            } else {
                throw new InstagramException(__("Received incorrect data from Instagram!"));
            }
        }
        return null;
    }

    public function getUserMedia(
        $profile = '',
        $limit = 0
    ) {
        if (empty($profile)) {
            $profile = $this->profile_name;
        }
        $user = $this->_parseBase($this->_makeBase(self::BASE_URL . $profile . '/'));
        $items = [];
        if (!empty($user)) {
            $edges = $user['edge_owner_to_timeline_media']['edges'];
            foreach ($edges as $item) {
                $items[] = $this->_prepareItem($item);
                if ($limit > 0 && $limit <= count($items)) {
                    break;
                }
            }
        }
        return $items;
    }

    public function _prepareItem($data)
    {
        if (isset($data["node"])) {
            $data = $data["node"];
        }

        $result = [
            'ints_id' => $data['id'],
            'owner' => $data['owner']['username'],
            'typename' => strtoupper(str_replace('Graph', '', $data['__typename'])),
            'shortcode' => $data['shortcode'],
            'dimensions_width' => $data['dimensions']['width'],
            'dimensions_height' => $data['dimensions']['height'],
            'display_url' => $this->unescapeUTF8EscapeSeq($data['display_url']),
            'edge_media_to_caption' => !empty($data['edge_media_to_caption']['edges']) ? $this->unescapeUTF8EscapeSeq($data['edge_media_to_caption']['edges'][0]['node']['text']) : '',
            'edge_media_to_comment' => $data['edge_media_to_comment']['count'],
            'taken_at_timestamp' => date('Y-m-d H:i:s', $data['taken_at_timestamp']),
            'edge_liked_by' => $data['edge_liked_by']['count'],
            'edge_media_preview_like' => $data['edge_media_preview_like']['count'],
            'location' => !empty($data['location']) ? $data['location']['name'] : null,
            'video_view_count' => $data['video_view_count'] ?? 0,
//            'thumbnail_src' => $this->unescapeUTF8EscapeSeq($data['thumbnail_src']),
//            'thumbnail_src_320' => '',
//            'thumbnail_src_480' => '',
//            'thumbnail_src_640' => '',
        ];
        if ('Sidecar' == $result['typename']) {
            $result['typename'] = 'CAROUSEL_ALBUM';
        }

//        if (isset($data['thumbnail_resources']) && is_array($data['thumbnail_resources'])) {
//            foreach ($data['thumbnail_resources'] as $resource) {
//                $url = $this->unescapeUTF8EscapeSeq($resource['src']);
//                switch ($resource['config_width']) {
//                    case 320:
//                        $result['thumbnail_src_320'] = $url;
//                        break;
//                    case 480:
//                        $result['thumbnail_src_480'] = $url;
//                        break;
//                    case 640:
//                        $result['thumbnail_src_640'] = $url;
//                        break;
//                }
//            }
//        }
        if ('CAROUSEL_ALBUM' == $result['typename'] && isset($data['edge_sidecar_to_children'])) {
            $sidecars = [];
            foreach ($data['edge_sidecar_to_children']['edges'] as $sidecar) {
                $sidecars[] = $this->unescapeUTF8EscapeSeq($sidecar['node']['display_url']);
            }
            $result['display_url'] = $sidecars;
        }
        return $result;
    }

    private function unescapeUTF8EscapeSeq($str)
    {
        return preg_replace_callback("/\\\u([0-9a-f]{4})/i", function ($matches) {
            return html_entity_decode("&#x" . $matches[1] . ";", ENT_QUOTES, "UTF-8");
        }, $str);
    }

}