<?php
/**
 * Created by L.
 * Author: wxuns
 * Link: https://www.wxuns.cn
 * Date: 2019/7/17
 * Time: 17:30
 */

namespace Wxuns\Yunmianqian;

use GuzzleHttp\Client;
use function PHPSTORM_META\type;
use Wxuns\Yunmianqian\Exceptions\HttpException;
use Wxuns\Yunmianqian\Exceptions\InvalidArgumentException;
use Wxuns\Yunmianqian\Exceptions\MissArgumentException;

class Yunmianqian
{
	protected $app_id;
	protected $app_secret;

	public function __construct($app_id,$app_secret)
	{
		$this->app_id = $app_id;
		$this->app_secret = $app_secret;
	}

	public function getHttpClient()
    {
        return new Client(['base_uri' => 'https://open.yunmianqian.com']);
    }

    /**
     * Place an order.
     * @param array $options
     * @param bool $cache
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function order(array $options,$cache = true,$price_type = 'floor')
    {
        $options['app_id'] = $this->app_id;
        $options['sign'] = $this->sign($options);
        $options['multiple'] = [
            'headers' => ['content-type'=>'application/x-www-form-urlencoded']
        ];
        if (!is_bool($cache)){
            throw new InvalidArgumentException('Invalid response cache:'.$cache);
        }
        if (!in_array($price_type,['floor','ceil'])){
            throw new InvalidArgumentException('Invalid response price_type:'.$price_type);
        }
        $url = \sprintf('/api/pay?order_cache=%s&price_type=%s',$cache?"true":"false",$price_type);
        try{
            $response = $this->getHttpClient()->post($url,['form_params'=>array_filter($options)])->getBody()->getContents();
            return $response;
        }catch (\Exception $e){
            throw new HttpException($e->getMessage(),$e->getCode(),$e);
        }
    }
    public function handleScannedNotify($func)
    {

    }

    /**
     * 订单查询
     * @param $order_sn
     * @return string
     * @throws HttpException
     * @throws InvalidArgumentException
     */
    public function queryOrder($order_sn)
    {
        if (!$order_sn){
            throw new InvalidArgumentException('Invalid response order_sn.');
        }
        try{
            $response = $this->getHttpClient()->post('/api/query',[
                'form_params'=>[
                    'app_id'   => $this->app_id,
                    'order_sn' => $order_sn,
                    'sign'     => $this->querySign($order_sn),
                    'multiple' => [
                        'headers' => ['content-type'=>'application/x-www-form-urlencoded']
                    ]
                ]
            ])->getBody()->getContents();
            return $response;
        }catch (\Exception $e){
            throw new HttpException($e->getMessage(),$e->getCode(),$e);
        }
    }

    /**
     * 云端状态查询
     * @return string
     * @throws HttpException
     */
    public function cloudStatus()
    {
        try{
            $response = $this->getHttpClient()->post('/api/cloud',[
                'form_params'=>[
                    'app_id'   => $this->app_id,
                    'sign'     => md5($this->app_id.$this->app_secret),
                    'multiple' => [
                        'headers' => ['content-type'=>'application/x-www-form-urlencoded']
                    ]
                ]
            ])->getBody()->getContents();
            return $response;
        }catch (\Exception $e){
            throw new HttpException($e->getMessage(),$e->getCode(),$e);
        }
    }
    /**
     * To sign.
     * @param $optins
     * @return string
     */
    public function sign(array $optins)
    {
        try{
            return md5($this->app_id.$optins['out_order_sn'].$optins['name'].$optins['pay_way'].$optins['price'].(isset($optins['attach'])?$optins['attach']:'').$optins['notify_url'].$this->app_secret);
        }catch (\Exception $e){
            throw new MissArgumentException('Invalid options.');
        }
    }

    /**
     * 订单查询签名
     * @param $order_sn
     * @return string
     */
    public function querySign($order_sn)
    {
        return md5($this->app_id.$order_sn.$this->app_id);
    }
}
