<?php


namespace Olegnax\Athlete2\Model;


use Closure;
use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem\Directory\ReadFactory;

class Api
{
    const THEME_PATH = 'frontend/Olegnax/athlete2';

    public static function activate(Client $a, Closure $b, $c)
    {
        // Prepare
        $d = $b();
        if (!is_a($d, Request::class)) {
            throw new Exception(__('Closure must return an object instance of Request.'));
        }
        // Call
        if (!array_key_exists('domain', $d->request) || empty($d->request['domain'])) {
            $d->request['domain'] = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'Unknown';
        }
        $d->request['domain'] = preg_replace('/^(www|dev)\./i', '', $d->request['domain']);
        $d->request['meta'] = static::getMeta();
        $d->request['meta']['activate_user_ip'] = $d->request['meta']['user_ip'];
        unset($d->request['meta']['user_ip']);
        $e = $a->call(base64_decode('bGljZW5zZV9rZXlfYWN0aXZhdGU='), $d);
        if ($e && isset($e->error)
            && $e->error === false
        ) {
            if (isset($e->notices)) {
                $d->notices = (array)$e->notices;

            }
            $d->data = (array)$e->data;
            $d->touch();
            call_user_func($c, (string)$d);
        }

        return $e;
    }

    private static function getMeta()
    {
        return [
            'user_ip' => static::getClientIp(),
            'magento_version' => static::getMagentoVersion(),
            'theme_version' => static::getThemeVersion(static::THEME_PATH),
        ];
    }

    public static function getClientIp()
    {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } else {
            if (isset($_SERVER['REMOTE_ADDR'])) {
                return $_SERVER['REMOTE_ADDR'];
            } else {
                if (isset($_SERVER['REMOTE_HOST'])) {
                    return $_SERVER['REMOTE_HOST'];
                }
            }
        }

        return 'UNKNOWN';
    }

    private static function getMagentoVersion()
    {
        return ObjectManager::getInstance()->get(ProductMetadataInterface::class)->getVersion();
    }

    private static function getThemeVersion($a)
    {
        /** @var ComponentRegistrarInterface $b */
        $b = ObjectManager::getInstance()->get(ComponentRegistrarInterface::class);
        $c = $b->getPath(ComponentRegistrar::THEME, $a);
        if ($c) {
            /** @var ReadFactory $d */
            $d = ObjectManager::getInstance()->get(ReadFactory::class);
            $e = $d->create($c);
            if ($e->isExist('composer.json')) {
                $f = $e->readFile('composer.json');
                $f = json_decode($f, true);
                if (isset($f['version'])) {
                    return $f['version'];
                }
            }
        }
        return '';
    }

    public static function validate(
        Client $a,
        Closure $b,
        $c,
        $d = null,
        $f = false,
        $g = false,
        $h = 2,
        $i = '+1 hour'
    ) {
        // Prepare
        $j = $b();
        if (!is_a($j, Request::class)) {
            throw new Exception(' \Closure must return an object instance of Request.');
        }
        $j->updateVersion();
        // Check j data

        if ($j->isEmpty || empty($j->data['the_key'])) {
            return false;
        }
        // No need to check if j already expired.
        if ('active' != $j->data['status']) {
            return false;
        }
        // Validate cached j data
        if (!$f
            && time() < $j->nextCheck
            && $j->isValid
        ) {
            return true;
        }
        // Call
        if (empty($d)) {
            $d = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'Unknown';
        }
        $j->request['domain'] = preg_replace('/^(www|dev)\./i', '', $d);
        $j->request['meta'] = static::getMeta();
        $k = null;
        try {
            $k = $a->call(base64_decode('bGljZW5zZV9rZXlfdmFsaWRhdGU='), $j);
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Could not resolve host') === false) {
                throw $e;
            }
        }

        if ($k
            && isset($k->error)
        ) {
            if (isset($k->data)) {
                $j->data = (array)$k->data;
            } else {
                $j->data = [];
            }
            if ($k->error && isset($k->errors)) {
                $j->data = ['errors' => $k->errors];
            }
            $j->touch();
            call_user_func($c, (string)$j);
            return $k->error === false;
        } else {
            if (empty($k)
                && $j->url
                && isset($j->data['allow_offline'])
                && isset($j->data['offline_interval'])
                && isset($j->data['offline_value'])
                && $j->data['allow_offline'] === true
            ) {
                if (!$j->isOffline) {
                    $j->enableOffline();
                    call_user_func($c, (string)$j);
                    return true;
                } else {
                    if ($j->isOfflineValid) {
                        return true;
                    }
                }
            } else {
                if (empty($k)
                    && $g
                    && $j->retries < $h
                ) {
                    $j->addRetryAttempt($i);
                    call_user_func($c, (string)$j);
                    return true;
                }
            }
        }
        return false;
    }

    public static function deactivate(Client $a, Closure $b, $c, $d = null)
    {
        // Prepare
        $e = $b();
        if (!is_a($e, Request::class)) {
            throw new Exception(' \Closure must return an object instance of Request.');
        }
        $e->updateVersion();
        // Call
        if (empty($d)) {
            $d = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'Unknown';
        }
        $e->request['domain'] = preg_replace('/^(www|dev)\./i', '', $d);
        $e->request['meta'] = static::getMeta();
        $f = $a->call(base64_decode('bGljZW5zZV9rZXlfZGVhY3RpdmF0ZQ=='), $e);
        // Remove e
        if ($f && isset($f->error)) {
            if ($f->error === false) {
                call_user_func($c, null);
            } else {
                if (isset($f->errors)) {
                    foreach ($f->errors as $key => $message) {
                        if ($key === base64_decode('YWN0aXZhdGlvbl9pZA==')) {
                            call_user_func($c, null);
                            break;
                        }
                    }
                }
            }
        }
        return $f;
    }

    public static function softValidate(Closure $a)
    {
        // Prepare
        $b = $a();
        if (!is_a($b, Request::class)) {
            throw new Exception(' \Closure must return an object instance of Request.');
        }
        $b->updateVersion();
        // Check b data
        if ($b->isEmpty || !$b->data['the_key']) {
            return false;
        }
        if ('active' != $b->data['status']) {
            return false;
        }
        // Validate cached b data
        return $b->isValid;
    }

    public static function check(Client $a, Closure $b, $c)
    {
        // Prepare
        $d = $b();
        if (!is_a($d, Request::class)) {
            throw new Exception(' \Closure must return an object instance of Request.');
        }
        $d->updateVersion();
        // Check d data
        if ($d->isEmpty || !$d->data['the_key']) {
            return false;
        }
        if ('active' != $d->data['status']) {
            return false;
        }
        // Call
        $d->request['domain'] = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'Unknown';
        $f = null;
        try {
            $f = $a->call('license_key_validate', $d);
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Could not resolve host') === false) {
                throw $e;
            }
        }
        if ($f && isset($f->error)) {
            if (isset($f->data)) {
                $d->data = (array)$f->data;
            } else {
                $d->data = [];
            }
            if ($f->error && isset($f->errors)) {
                $d->data = ['errors' => $f->errors];
            }
            $d->touch();

            call_user_func($c, (string)$d);
        }
        return $f;
    }
}
