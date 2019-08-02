<?php
/**
 * Created by PhpStorm.
 * Author: wxuns <wxuns@wxuns.cn>
 * Link: http://wxuns.cn
 * Date: 2019/7/17
 * Time: 21:44
 */

namespace Wxuns\Yunmianqian\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Wxuns\Yunmianqian\Exceptions\InvalidArgumentException;
use Wxuns\Yunmianqian\Exceptions\MissArgumentException;
use Wxuns\Yunmianqian\Yunmianqian;

class YunmianqianTest extends TestCase
{
    /**
     * 测试签名参数
     * @throws MissArgumentException
     */
    public function testSignWithInvalidArgument()
    {
        $yunmianqian = new Yunmianqian('app_id','app_serect');
        $this->expectException(MissArgumentException::class);
        $this->expectExceptionMessage('Invalid options.');
        $yunmianqian->sign([]);
        $this->fail('Failed to assert sign throw exception with invalid argument.');
    }

    /**
     * 测试order参数，options参数已在签名中测试
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testOrderWithInvalidArgument()
    {
        $yunmianqian = new Yunmianqian('app_id','app_serect');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid response cache:foo');
        $yunmianqian->order([
            'out_order_sn'=>1111,
            'name'=>'测试商品',
            'pay_way'=>'alipay',
            'price'=>10,
            'notify_url'=>'https://...'
        ],'foo');
        $this->fail('Failed to assert cache throw exception with invalid argument.');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid response price_type:foo');
        $yunmianqian->order([
            'out_order_sn'=>1111,
            'name'=>'测试商品',
            'pay_way'=>'alipay',
            'price'=>10,
            'notify_url'=>'https://...'
        ],true,'foo');
        $this->fail('Failed to assert pricetype throw exception with invalid argument.');
    }

    public function testOrder()
    {
        $response = new Response(200,[],'{"code":200,"msg":"success","data":{}}');
        $client = \Mockery::mock(Client::class."['request']");
        $options = [
            'out_order_sn'=>1111,
            'name'=>'测试商品',
            'pay_way'=>'alipay',
            'price'=>10,
            'notify_url'=>'https://...',
            'sign'=>'sign'
        ];
        $cache = true;$price_type = 'floor';
        $url = \sprintf('https://open.yunmianqian.com/api/pay?order_cache=true&price_type=%s',$price_type);

        $client->expects()->request('POST',$url,['form_params' =>array_filter($options)])->andReturn($response);
        $ymq = \Mockery::mock(Yunmianqian::class,['app_id','app_secret'])->makePartial();
//        $ymq->allows()->getHttpClient()->andReturn($client);
        $this->assertSame(['success' => true], $ymq->order($options,$cache,$price_type));
    }
}
