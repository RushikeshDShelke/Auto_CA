<?php


namespace Olegnax\InstagramMin\Model\Token;


use Exception;

class InstagramAPI
{
    const URL_BASE_API = "https://api.instagram.com/";
    const URL_BASE_GRAPH = "https://graph.instagram.com/";


    const URL_PATH_AUTHORIZE = "oauth/authorize";
    const URL_PATH_ACCESSTOKEN = "oauth/access_token";
    /**
     * @var int
     */
    public $userId;
    /**
     * @var string
     */
    public $clientId;
    /**
     * @var string
     */
    public $redirectUri;
    /**
     * @var string
     */
    public $clientSecret;
    /**
     * @var string
     */
    protected $accessToken;

    /**
     * Instagram constructor.
     *
     * @param string $clientId
     * @param string $clientSecret
     */
    public function __construct($clientId = "", $clientSecret = "")
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * @param string $state
     * @param string[] $scope
     *
     * @return string
     */
    public function getOAuthUrl($state = "", $scope = ["user_profile", "user_media"])
    {
        $args = [
            "client_id" => $this->clientId,
            "redirect_uri" => $this->redirectUri,
            "scope" => implode(",", $scope),
            "response_type" => "code",
            "state" => $state,
        ];
        $args = array_filter($args);
        $args = http_build_query($args);

        return static::URL_BASE_API . static::URL_PATH_AUTHORIZE . "?" . $args;
    }

    public function getToken($code)
    {
        $data = $this->getShortToken($code);
        $this->setUserId($data["user_id"]);
        $data = $this->getLongToken($data["access_token"]);

        if (isset($data["access_token"])) {
            $this->setToken($data["access_token"]);
        }

        return $data;
    }

    /**
     * @param string $code
     * @param string $redirectUri
     *
     * @return bool|array
     */
    public function getShortToken($code)
    {
        $data = $this->curl([
            CURLOPT_URL => static::URL_BASE_API . static::URL_PATH_ACCESSTOKEN,
            CURLOPT_POSTFIELDS => [
                "client_id" => $this->clientId,
                "client_secret" => $this->clientSecret,
                "grant_type" => "authorization_code",
                "redirect_uri" => $this->redirectUri,
                "code" => $code,
            ],
        ], "post", "array");

        if (empty($data)) {
            throw new Exception("Nothing recieved!");
        }
        if (isset($data["error_message"])) {
            throw new Exception($data["error_message"], $data["code"]);
        }

        return $data;
    }

    /**
     * @param array $options
     * @param string $method
     *
     * @return bool|string
     */
    private function curl(
        $options,
        $method = "GET",
        $to = "string"
    ) {
        $default = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_TIMEOUT => 0,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ];
        $options = array_replace($default, $options);

        $options[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
        switch ($options[CURLOPT_CUSTOMREQUEST]) {
            case "POST":
                break;
            case "GET":
            default:
                $options[CURLOPT_CUSTOMREQUEST] = "GET";
                if (isset($options[CURLOPT_POSTFIELDS])) {
                    if (is_array($options[CURLOPT_POSTFIELDS])) {
                        $options[CURLOPT_POSTFIELDS] = http_build_query($options[CURLOPT_POSTFIELDS]);
                    }
                    $options[CURLOPT_URL] .= "?" . (string)$options[CURLOPT_POSTFIELDS];
                }
        }

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $data = curl_exec($ch);
        curl_close($ch);

        switch ($to) {
            case "array":
                if (!empty($data)) {
                    $data = json_decode($data, true);
                }
                break;
            case "object":
                if (!empty($data)) {
                    $data = json_decode($data);
                }
        }

        return $data;
    }

    public function setUserId($userId)
    {
        if (0 < (int)$userId) {
            $this->userId = (int)$userId;
        }

        return $this;
    }

    public function getLongToken($accesToken)
    {
        return $this->_longTonke($accesToken, "access_token");
    }

    private function _longTonke($accesToken, $action = "access_token")
    {
        $isRefresh = $action === "refresh_access_token";
        $data = $this->curl([
            CURLOPT_URL => static::URL_BASE_GRAPH . ($isRefresh ? "refresh_access_token" : "access_token"),
            CURLOPT_POSTFIELDS => [
                "client_secret" => $this->clientSecret,
                "grant_type" => "ig_exchange_token",
                "access_token" => $accesToken,
            ],
        ], "get", "array");

        if (isset($data["error"])) {
            throw new Exception($data["error"]["message"], isset($data["error"]["code"]) ? $data["error"]["code"] : 501);
        }

        $data["expire"] = time() + $data["expires_in"];

        return $data;
    }

    public function setToken($accesToken)
    {
        if (!empty($accesToken)) {
            $this->accessToken = $accesToken;
        }

        return $this;
    }

    public function refreshToken($accesToken)
    {
        return $this->_longTonke($accesToken, "refresh_access_token");
    }

    /**
     * @param $redirectUri
     *
     * @return $this
     */
    public function setRedirect($redirectUri)
    {
        $this->redirectUri = $redirectUri;

        return $this;
    }

    public function getMe($fields = [])
    {
        return $this->getUser(null, $fields);
    }

    public function getUser($userId = null, $fields = [])
    {
        if (empty($userId)) {
            $userId = "me";
        }

        $data = $this->curl([
            CURLOPT_URL => static::URL_BASE_GRAPH . $userId,
            CURLOPT_POSTFIELDS => array_filter([
                "fields" => implode(",", $fields),
                "access_token" => $this->accessToken,
            ]),
        ], "get", "array");

        if (isset($data["error"])) {
            throw new Exception($data["error"]["message"], isset($data["error"]["code"]) ? $data["error"]["code"] : 500);
        }

        return $data;
    }

    public function getUserMedia($userId = null, $fields = [], $limit = 25, $after = "")
    {
        if (empty($userId)) {
            $userId = $this->userId;
        }
        $data = $this->curl([
            CURLOPT_URL => static::URL_BASE_GRAPH . $userId . '/media',
            CURLOPT_POSTFIELDS => array_filter([
                "fields" => implode(",", $fields),
                "access_token" => $this->accessToken,
                "pretty" => 1,
                "limit" => $limit,
                "after" => $after,
            ]),
        ], "get", "array");

        if (isset($data["error"])) {
            throw new Exception($data["error"]["message"], isset($data["error"]["code"]) ? $data["error"]["code"] : 500);
        }

        return $data;
    }

    public function getMedia($mediaId = null, $fields = [])
    {
        $data = $this->curl([
            CURLOPT_URL => static::URL_BASE_GRAPH . $mediaId,
            CURLOPT_POSTFIELDS => array_filter([
                "fields" => implode(",", $fields),
                "access_token" => $this->accessToken,
            ]),
        ], "get", "array");

        if (isset($data["error"])) {
            throw new Exception($data["error"]["message"], isset($data["error"]["code"]) ? $data["error"]["code"] : 500);
        }

        return $data;
    }

}