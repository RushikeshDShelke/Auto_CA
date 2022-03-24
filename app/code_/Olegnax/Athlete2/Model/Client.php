<?php


namespace Olegnax\Athlete2\Model;

use Exception;

class Client
{

    protected static $instance;

    protected $a;

    protected $b = [];

    private $c;

    public static function instance()
    {
        if (isset(static::$instance)) {
            return static::$instance;
        }
        static::$instance = new self;
        return static::$instance;
    }

    public function on($a, $b)
    {
        if (!is_array($this->b)) {
            $this->b = [];
        }
        if (is_callable($b)) {
            $this->b[$a] = $b;
        }
        return $this;
    }

    public function call($a, Request $b, $c = 'POST')
    {
        $d = microtime(true);
        $this->trigger('start', [$d]);
        // Begin
        $this->setCurl(preg_match('/https\:/', $b->url));
        $this->resolveEndpoint($a, $b);
        // Make call
        $url = $b->url . $a;
        $this->trigger('endpoint', [$a, $url]);
        curl_setopt($this->c, CURLOPT_URL, $url);
        // Set method
        $this->trigger('request', [$b->request]);
        switch ($c) {
            case 'GET':
                curl_setopt($this->c, CURLOPT_POST, 0);
                break;
            case 'POST':
                curl_setopt($this->c, CURLOPT_POST, 1);
                if ($b->request && count($b->request) > 0) {
                    curl_setopt($this->c, CURLOPT_POSTFIELDS, http_build_query($b->request));
                }
                break;
            case 'JPOST':
            case 'JPUT':
            case 'JGET':
            case 'JDELETE':
                $json = json_encode($b->request);
                curl_setopt($this->c, CURLOPT_CUSTOMREQUEST, preg_replace('/J/', '', $c, -1));
                curl_setopt($this->c, CURLOPT_POSTFIELDS, $json);
                // Rewrite headers
                curl_setopt($this->c, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($json),
                ]);
                break;
        }
        // Get response
        $this->a = curl_exec($this->c);
        if (curl_errno($this->c)) {
            $error = curl_error($this->c);
            curl_close($this->c);
            if (!empty($error)) {
                throw new Exception($error);
            }
        } else {
            curl_close($this->c);
        }
        $this->trigger('response', [$this->a]);
        $this->trigger('finish', [microtime(true), $d]);
        return empty($this->a) ? null : json_decode($this->a);
    }

    private function trigger($a, $b = [])
    {
        if (array_key_exists($a, $this->b)) {
            call_user_func_array($this->b[$a], $b);
        }
    }

    private function setCurl($a = false)
    {
        // Init
        $this->c = curl_init();
        // Sets basic parameters
        curl_setopt($this->c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->c, CURLOPT_TIMEOUT, isset($this->request->settings['timeout']) ? $this->request->settings['timeout'] : 100);
        // Set parameters to maintain cookies across sessions
        curl_setopt($this->c, CURLOPT_COOKIESESSION, true);
        curl_setopt($this->c, CURLOPT_COOKIEFILE, sys_get_temp_dir() . '/cookies_file');
        curl_setopt($this->c, CURLOPT_COOKIEJAR, sys_get_temp_dir() . '/cookies_file');
        curl_setopt($this->c, CURLOPT_USERAGENT, 'OLEGNAX-PURCHASE-VERIFY');
        if ($a) {
            $this->setSSL();
        }
    }

    private function setSSL()
    {
        curl_setopt($this->c, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->c, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->c, CURLOPT_RETURNTRANSFER, 1);
    }

    private function resolveEndpoint(&$a, Request $b)
    {
        switch ($b->handler) {
            case 'wp_rest':
                $a = base64_decode('L3dwLWpzb24vd29vLWxpY2Vuc2Uta2V5cy92MS8=')
                    . str_replace(base64_decode('bGljZW5zZV9rZXlf'), '', $a);
                break;
            default:
                $a = '?action=' . $a;
                break;
        }
    }
}