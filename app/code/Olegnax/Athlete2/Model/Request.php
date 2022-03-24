<?php


namespace Olegnax\Athlete2\Model;


use Exception;

class Request
{

    const DAILY_FREQUENCY = 'daily';

    const HOURLY_FREQUENCY = 'hourly';

    const WEEKLY_FREQUENCY = 'weekly';

    protected $settings = [];

    protected $request = [];

    protected $data = [];

    protected $notices = [];

    protected $meta = [];

    public function __construct($a)
    {
        $a = json_decode($a);
        if (isset($a->settings)) {
            $this->settings = (array)$a->settings;
        }
        if (isset($a->request)) {
            $this->request = (array)$a->request;
        }
        if (isset($a->data)) {
            $this->data = (array)$a->data;
        }
        if (isset($a->meta)) {
            $this->meta = (array)$a->meta;
        }
        // Check for activation_id
        if (!isset($this->request['activation_id'])
            && isset($this->data['activation_id'])
        ) {
            $this->request['activation_id'] = $this->data['activation_id'];
        }
        if (!isset($this->request['license_key'])
            && isset($this->data['the_key'])
        ) {
            $this->request['license_key'] = $this->data['the_key'];
        }
    }

    public static function create($a, $b, $c, $d, $e = '', $f = self::DAILY_FREQUENCY, $g = null)
    {
        $h = [
            'settings' => [
                'url' => $a,
                'frequency' => $f === null ? self::DAILY_FREQUENCY : $f,
                'next_check' => 0,
                'version' => '1.2.0',
                'retries' => 0,
                'handler' => $g,
            ],
            'request' => [
                'store_code' => $b,
                'sku' => $c,
                'license_key' => $d,
                'domain' => $e,
            ],
            'data' => [],
            'meta' => [],
        ];
        return new self(json_encode($h));
    }

    public function &__get($a)
    {
        $b = null;
        switch ($a) {
            case 'url':
                if (isset($this->settings['url'])) {
                    return $this->settings['url'];
                }
                break;
            case 'frequency':
                if (isset($this->settings['frequency'])) {
                    return $this->settings['frequency'];
                }
                break;
            case 'nextCheck':
                if (isset($this->settings['next_check'])) {
                    return $this->settings['next_check'];
                }
                break;
            case 'request':
                return $this->request;
            case 'data':
                return $this->data;
            case 'notices':
                return $this->notices;
            case 'isOffline':
                $b = isset($this->settings['offline']);
                break;
            case 'isOfflineValid':
                $b = $this->isOffline
                    && ($this->settings['offline'] === true
                        || time() < $this->settings['offline']
                    );
                break;
            case 'isValid':
                $b = false;
                if (isset($this->settings['frequency'])
                    && !empty($this->settings['frequency'])
                    && isset($this->data)
                    && !empty($this->data)
                    && is_numeric($this->data['activation_id'])
                ) {
                    $b = $this->data['the_key'] !== null && 'active' ===  $this->data['status'];
                }
                break;
            case 'isEmpty':
                $b = empty($this->data);
                break;
            case 'version':
                if (isset($this->settings['version'])) {
                    return $this->settings['version'];
                }
                break;
            case 'retries':
                if (isset($this->settings['retries'])) {
                    return $this->settings['retries'];
                }
                break;
            case 'handler':
                if (isset($this->settings['handler'])) {
                    return $this->settings['handler'];
                }
                break;
        }
        return $b;
    }

    public function __set($a, $b)
    {
        switch ($a) {
            case 'data':
                $this->data = $b;
                break;
            case 'notices':
                $this->notices = $b;
                break;
        }
    }

    public function __toString()
    {
        return json_encode([
            'settings' => $this->settings,
            'request' => $this->request,
            'data' => $this->data,
            'notices' => $this->notices,
            'notice' => $this->getNotice(),
            'meta' => $this->meta,
        ]);
    }

    public function getNotice()
    {
        $a = '';
        if (isset($this->notices)) {
            foreach ($this->notices as $notice_group => $notices) {
                $a .= sprintf('<span data-group="%s">%s</span> ', $notice_group, implode(' ', $notices));
            }
            $a = trim($a);
        }

        return $a;
    }

    public function enableOffline()
    {
        $this->settings['offline'] = $this->data['offline_interval'] === 'unlimited'
            ? true
            : strtotime('+' . intval($this->data['offline_value']) . ' ' . $this->data['offline_interval']);
        $this->touch(false);
    }

    public function touch($a = true)
    {
        $this->settings['retries'] = 0;
        if ($a && isset($this->settings['offline'])) {
            unset($this->settings['offline']);
        }
        if (!isset($this->settings['frequency'])) {
            return;
        }
        switch ($this->settings['frequency']) {
            case self::DAILY_FREQUENCY:
                $this->settings['next_check'] = strtotime('+1 days');
                break;
            case self::HOURLY_FREQUENCY:
                $this->settings['next_check'] = strtotime('+1 hour');
                break;
            case self::WEEKLY_FREQUENCY:
                $this->settings['next_check'] = strtotime('+1 week');
                break;
            default:
                $this->settings['next_check'] = strtotime($this->settings['frequency']);
                break;
        }
    }

    public function addRetryAttempt($a)
    {
        $this->settings['retries']++;
        $this->settings['next_check'] = strtotime($a);
    }

    public function updateVersion()
    {
        switch ($this->version) {
            case null:
                $this->settings['version'] = '1.0.6';
                $this->settings['retries'] = 0;
                break;
            case '1.0.6':
                $this->settings['version'] = '1.1.0';
                $this->meta = [];
                break;
            case '1.1.0':
                $this->settings['version'] = '1.2.0';
                $this->settings['handler'] = null;
                break;
        }
    }
    public function add($a, $b = null)
    {
        if (!is_string($a)) {
            throw new Exception('Meta key must be a string.');
        }
        if (!is_array($this->meta)) {
            $this->meta = [];
        }
        $this->meta[$a] = is_object($b) ? (array)$b : $b;
    }

    public function get($a, $b = null)
    {
        if (!is_string($a)) {
            throw new Exception('Meta key must be a string.');
        }
        return array_key_exists($a, $this->meta) ? $this->meta[$a] : $b;
    }
}
