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
    protected $guzzleOptions = [];

	public function __construct($app_id,$app_secret)
	{
		$this->appid = $app_id;
		$this->app_secret = $app_secret;
	}

	public function getHttpClient()
    {
        return new Client($this->guzzleOptions);
    }

    public function setGuzzleOptions(array $options)
    {
        $this->guzzleOptions = $options;
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
        $url = \sprintf('https://open.yunmianqian.com/api/pay?order_cache=%s&price_type=%s',$cache?"true":"false",$price_type);

        try{
            $response = $this->getHttpClient()->request('POST',$url,[
                'form_params' =>array_filter($options),
            ])->getBody()->getContents();
            return json_decode($response);
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
}
