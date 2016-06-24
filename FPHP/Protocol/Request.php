<?php
/**
 * 请求
 * User: 30feifei@gamil.com
 * Date: 2016/6/22
 * Time: 18:05
 */

namespace FPHP\Protocol;


class Request {
    private static $params=array();

    public static function setParams($params){
        self::$params = $params;
    }

    public static function getMethod(){
        if(!empty(self::$params['r'])){
            return explode('.',self::$params['r']);
        }
        return array('Index','index');
    }

    /**
     * @param        $url
     * @param string $postData
     * @param int    $timeOut
     * @param string $proxy
     * @return bool|mixed
     */
    public static function curl($url, $postData = '', $timeOut = 6, $proxy = ''){
        $ch = curl_init();
        $options = array(
            CURLOPT_USERAGENT      => "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)",
            CURLOPT_TIMEOUT        => $timeOut,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => true,
        );

        //代理
        if ($proxy) {
            $options[CURLOPT_PROXY] = $proxy;
            $options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5;
        }

        //post
        if (!empty($postData)) {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $postData;
        }

        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        return $info['http_code'] !== 200 ? false : $result;
    }
}