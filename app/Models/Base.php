<?php
/**
 * Created by PhpStorm.
 * User: xiaoxiaonan
 * Date: 2019/7/16
 * Time: 11:00
 */

namespace App\Models;

use http\Cookie;
use Illuminate\Database\Eloquent\Model;

class Base extends Model
{
    protected $base_url = 'https://qyapi.weixin.qq.com/';

    protected function buildReturn($state, $message = '', $data = array())
    {
        return [
            'state' => strval($state),
            'message' => strval($message),
            'data' => $data
        ];
    }


    // 发送网络请求
    protected function httpRequest($url, $header = array(), $post_data = array(), $cookie = '')
    {
//        file_put_contents('/opt/ci123/www/html/sc-edu/tmp/wx/qywx_third/visitor/httpRequest.log', date('Y-m-d H:i:s')."\t".$url."\n".var_export($post_data, 1)."\n", FILE_APPEND);

        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_AUTOREFERER => 1,
            CURLOPT_USERAGENT => 'Mozilla/5.0 AppleWebKit/537.36 Chrome/58.0.3029.81 Safari/537.36',
        );
        if ($cookie) {
            $options[CURLOPT_COOKIE] = $cookie;
        }
        if ($header) {
            $options[CURLOPT_HTTPHEADER] = $header;
        }
        if ($post_data) {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = http_build_query($post_data);
        }
        if (substr($url, 0, 5) == 'https') {
            $options[CURLOPT_SSL_VERIFYHOST] = 2;
            $options[CURLOPT_SSL_VERIFYPEER] = 0;
        }
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);
//        file_put_contents('/opt/ci123/www/html/sc-edu/tmp/wx/qywx_third/visitor/httpRequest.log', "----------------\n".$response."\n==================================\n", FILE_APPEND);
        return $response;
    }

    /**
     * 发送json格式请求
     * @param $url
     * @param $jsonStr
     * @return bool|string
     */
    public function httpRequestJson($url, $jsonStr)
    {
        if(is_array($jsonStr)){
            $jsonStr = json_encode($jsonStr);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($jsonStr)
            )
        );
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * 提取数组数据
     *
     * @param $key_list
     * @param $param
     * @return array
     */
    public function transferParam($key_list, $param)
    {
        $data = [];
        foreach ($key_list as $v) {
            $data[$v] = $param[$v];
        }
        return $data;
    }

    //POST提交数据
    public function https_post($url,$data,$ssl = false)
    {
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
        if($ssl) {
            curl_setopt ( $ch,CURLOPT_SSLCERT,$this->sslcert_path);
            curl_setopt ( $ch,CURLOPT_SSLKEY,$this->sslkey_path);
        }
        curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
        curl_setopt ( $ch, CURLOPT_AUTOREFERER, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return 'Errno: '.curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }
}