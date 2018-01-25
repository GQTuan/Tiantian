<?php

namespace console\models;

use Yii;
use common\models\Order;
use common\models\Product;
use common\helpers\StringHelper;

class GatherSina extends Gather
{
    public $urlPrefix = 'http://hq.sinajs.cn/list=';
    // 交易产品列表，格式为["表名" => "抓取链接参数名"]
    public $productList = [
        // 'hkhsi' => 'rt_hkHSI', // 恒生指数
        // 'if1609' => 'CFF_RE_IF1610', // IF
        // 'a50' => 'hf_CHA50CFD', // A50
        // 'ic1609' => 'CFF_RE_IC1610', // IC
        // 'cu1610' => 'CU1610', // 沪铜
        // 'ni1609' => 'NI1609', // 沪镍
        // 'rb1610' => 'RB1610', // 螺纹钢
        // 'ru1609' => 'RU1609', // 橡胶
        // 'rm1609' => 'RM1609', // 菜粕
        //'cl' => 'hf_CL', // 美原油
        //'gc' => 'hf_GC', // 美黄金
        // 'xpt' => 'hf_XPT', // 伦敦铂金
		'ng' => 'hf_NG', // 天然气
        // 'xag' => 'hf_XAG', // 伦敦银
        //'cu0' => 'CU0', //沪铜
        // 'dji' => 'gb_$dji', //道琼斯工业平均指数(.DJI)
        // 'ixic' => 'gb_ixic', //纳斯达克综合指数(.IXIC)
        // 'inx' => 'gb_$inx', //标普500指数(.INX)
        // 'sh300' => 'sh000300' //沪深300指数
    ];

    protected function G_XAG($html, $name)
    {
        $this->G_XPT($html, $name, '伦敦银');
    }

    protected function G_XPT($html, $name, $title = '伦敦铂金')
    {
        $arr = explode(',', $html);
        $data = [];
        $pow = 1;

        $price = $arr[2] * $pow;
        $zuoshou = $arr[8] * $pow;
        $diff = $price - $zuoshou;
        $diffRate = (($price - $zuoshou) / $zuoshou) * 100;
        //名称-最新价-涨跌-涨跌幅-开盘价-最高-最低-昨收-更新时间
        $data = ['name' => $title, 'price' => $price, 'diff' => round($diff, 2), 'diff_rate' => round($diffRate, 2) . '%', 'open' => $arr[7] * $pow, 'close' => $zuoshou, 'low' => $arr[5] * $pow, 'high' => $arr[4] * $pow, 'time' => date('Y-m-d H:i:s')];
        
        $this->uniqueInsert($name, $data);
    }

    protected function G_SH300($html, $name)
    {
        $arr = explode(',', $html);
        $data = [];

        $price = $arr[3];
        $zuoshou = $arr[2];
        $diff = $price - $zuoshou;
        $diffRate = (($price - $zuoshou) / $zuoshou) * 100;
        //名称-最新价-涨跌-涨跌幅-开盘价-最高-最低-昨收-更新时间
        $data = ['name' => '沪深300指数', 'price' => str_replace(',', '', number_format($price, 2)), 'diff' => round($diff, 2), 'diff_rate' => round($diffRate, 2) . '%', 'open' => $arr[1], 'close' => $zuoshou, 'low' => $arr[5], 'high' => $arr[4], 'time' => $arr[30] . ' ' . $arr[31]];
        
        $this->insert($name, $data);
    }

    protected function G_INX($html, $name)
    {
        $this->G_DJI($html, $name, '标普指数');
    }

    protected function G_IXIC($html, $name)
    {
        $this->G_DJI($html, $name, '纳斯达克');
    }

    protected function G_DJI($html, $name, $title = '道琼斯')
    {
        $arr = explode(',', $html);
        $data = [];

        $price = $arr[1];
        $zuoshou = $arr[26];
        $diff = $price - $zuoshou;
        $diffRate = (($price - $zuoshou) / $zuoshou) * 100;
        //名称-最新价-涨跌-涨跌幅-开盘价-最高-最低-昨收-更新时间
        $data = ['name' => $title, 'price' => $price, 'diff' => round($diff, 2), 'diff_rate' => round($diffRate, 2) . '%', 'open' => $arr[5], 'close' => $zuoshou, 'low' => $arr[7], 'high' => $arr[6], 'time' => $arr[3]];
        
        $this->insert($name, $data);
    }

    protected function G_CU0($html, $name)
    {
        $this->G_RM1609($html, $name, '沪铜');
    }

    protected function G_HKHSI($html, $name)
    {
        // $html = $this->getHtml($this->url[$name]);
        $arr = explode(',', $html);
        $data = [];

        $price = $arr[6];
        $diff = $price - $arr[3];
        $diffRate = (($price - $arr[3]) / $arr[3]) * 100;
        //名称-最新价-涨跌-涨跌幅-开盘价-最高-最低-昨收-更新时间
        $data = ['name' => '恒生指数', 'price' => $price, 'diff' => round($diff, 2), 'diff_rate' => round($diffRate, 2) . '%', 'open' => $arr[2], 'close' => $arr[3], 'low' => $arr[5], 'high' => $arr[4], 'time' => date('Y-m-d', strtotime($arr[17])) . ' ' . $arr[18]];
        
        $this->insert($name, $data);
    }

    protected function G_IF1609($html, $name)
    {
        $this->G_IC1609($html, $name, 'IF');
    }

    protected function G_A50($html, $name)
    {
        $this->G_GC($html, $name, 'A50');
    }

    protected function G_IC1609($html, $name, $title = 'IC')
    {
        // $html = $this->getHtml($this->url[$name]);
        $arr = explode(',', $html);
        $data = [];

        $price = $arr[3];
        $diff = $price - $arr[13];
        $diffRate = (($price - $arr[13]) / $arr[13]) * 100;
        //名称-最新价-涨跌-涨跌幅-开盘价-最高-最低-昨收-更新时间
        $data = ['name' => $title, 'price' => $price, 'diff' => round($diff, 2), 'diff_rate' => round($diffRate, 2) . '%', 'open' => $arr[0], 'close' => $arr[13], 'low' => $arr[2], 'high' => $arr[1], 'time' => date('Y-m-d', strtotime($arr[36])) . ' ' . $arr[37]];
        
        $this->insert($name, $data);
    }

    protected function G_CU1610($html, $name)
    {
        $this->G_RM1609($html, $name, '沪铜');
    }

    protected function G_NI1609($html, $name)
    {
        $this->G_RM1609($html, $name, '沪镍');
    }

    protected function G_RB1610($html, $name)
    {
        $this->G_RM1609($html, $name, '螺纹钢');
    }

    protected function G_RU1609($html, $name)
    {
        $this->G_RM1609($html, $name, '橡胶');
    }

    protected function G_RM1609($html, $name, $title = '菜粕')
    {
        // $html = $this->getHtml($this->url[$name]);
        $arr = explode(',', $html);
        $data = [];

        $price = $arr[8];
        $diff = $price - $arr[10];
        $diffRate = (($price - $arr[10]) / $arr[10]) * 100;
        //名称-最新价-涨跌-涨跌幅-开盘价-最高-最低-昨收-更新时间
        $data = ['name' => $title, 'price' => $price, 'diff' => round($diff, 2), 'diff_rate' => round($diffRate, 2) . '%', 'open' => $arr[2], 'close' => $arr[10], 'low' => $arr[4], 'high' => $arr[3], 'time' => date('Y-m-d H:i:s')];
        
        $this->uniqueInsert($name, $data);
    }

    protected function G_CL($html, $name)
    {
        $this->G_GC($html, $name, '美原油');
    }
	
	protected function G_NG($html, $name)
    {
        $this->G_GC($html, $name, 'ng');
    }

    protected function G_GC($html, $name, $title = '美黄金')
    {
        // $html = $this->getHtml($this->url[$name]);
        $arr = explode(',', $html);
        $data = [];
		
		$agreeArr = ['xhn', 'conc'];
        if (in_array($title, $agreeArr)) {
            $arr[0] = $arr[0] * 100;
            $arr[4] = $arr[4] * 100;
            $arr[5] = $arr[5] * 100;
            $arr[7] = $arr[7] * 100;
            $arr[8] = $arr[8] * 100;
        }

		if (in_array($title, ['ng'])) {
            $arr[0] = $arr[0] * 1000;
            $arr[4] = $arr[4] * 1000;
            $arr[5] = $arr[5] * 1000;
            $arr[7] = $arr[7] * 1000;
            $arr[8] = $arr[8] * 1000;
        }
		
        $price = $arr[0];
        $diff = $price - $arr[7];
        $diffRate = (($price - $arr[7]) / $arr[7]) * 100;
        //名称-最新价-涨跌-涨跌幅-开盘价-最高-最低-昨收-更新时间
        $data = ['name' => $title, 'price' => $price, 'diff' => round($diff, 2), 'diff_rate' => round($diffRate, 2) . '%', 'open' => $arr[8], 'close' => $arr[7], 'low' => $arr[5], 'high' => $arr[4], 'time' => date('Y-m-d', strtotime($arr[12])) . ' ' . $arr[6]];

        $this->insert($name, $data);
    }

    public function run()
    {
        $this->switchMap = option('risk_product') ?: [];
        $params = $d = '';
        $products = Product::find()->andWhere(['product.state' => Product::STATE_VALID])->asArray()->map('id', 'table_name');
        foreach ($this->productList as $name => $url) {
            if (!in_array($name, $products)) {
                continue;
            }
            $params .= $d . $url;
            $d = ',';
        }
        $html = $this->getHtml($this->urlPrefix . $params);
        $pieces = StringHelper::explode("\n", $html);
        foreach ($pieces as $value) {
            preg_match('/var hq_str_(.*)="(.*)";/U', $value, $match);
            $link = $match[1];
            $html = $match[2];
            $name = array_search($link, $this->productList);
            $method = 'G_' . strtoupper($name);
            $this->$method($html, $name);
        }

        // 监听是否有人应该平仓
        $this->listen();
    }
}
