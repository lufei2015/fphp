<?php
/**
 *
 * User: 30feifei@gamil.com
 * Date: 2016/6/24
 * Time: 14:23
 */

namespace FPHP\Protocol;


class Response
{
    /**失败
     * @param       $state
     * @param       $msg
     * @param array $params
     */
    public static function fail($state, $msg, $params = array())
    {
        $retur = array('state' => $state, 'msg' => $msg);
        $retur = array_merge($retur, $params);
        echo self::encodeDate($retur);
        unset($retur);
        return;
    }

    /**成功
     * @param array $params
     */
    public static function success($params = array())
    {
        $retur = array('state' => 1);
        $retur = array_merge($retur, $params);
        echo self::encodeDate($retur);
        unset($retur);
        return;
    }

    /**
     * @param $data
     * @return string
     */
    public static function encodeDate($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param $json
     * @return mixed
     */
    public static function  decodeDate($json)
    {
        return json_decode($json, true);
    }
}