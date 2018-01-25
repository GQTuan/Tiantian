<?php

namespace admin\controllers;

use Yii;
use admin\models\User;
use admin\models\Order;
use admin\models\Product;
use admin\models\AdminUser;
use admin\models\UserCoupon;
use admin\models\UserCharge;
use admin\models\UserRebate;
use admin\models\UserWithdraw;
use admin\models\UserExtend;
use frontend\models\Bank;
use admin\models\AdminAccount;
use admin\models\UserGive;
use admin\models\Retail;
use admin\models\RetailWithdraw;
use common\helpers\Hui;
use common\helpers\Html;
use common\helpers\FileHelper;

class UserController extends \admin\components\Controller
{
    /**
     * @authname 会员列表
     */
    public function actionList()
    {
        $query = (new User)->listQuery()->manager()->orderBy('user.created_at DESC');

        $html = $query->getTable([
            ['type' => 'checkbox'],
            'id',
            'nickname' => ['type' => 'text'],
            'mobile',
            'pid' => ['header' => '推荐人', 'value' => function ($row) {
                return $row->getParentLink();
            }],
            //'admin.username' => ['header' => '用户上级'],
            'admin.id' => ['header' => '微圈', 'value' => function ($row) {
                if (!isset($row->admin->power)) {
                    return '';
                }
                return $row->admin->power == AdminUser::POWER_RING ? $row->admin->username : '无';
            }],
            'admin.username' => ['header' => '微会员', 'value' => function ($row) {
                if (!isset($row->admin->power)) {
                    return '';
                }
                if ($row->admin->power == AdminUser::POWER_MEMBER) {
                    return $row->admin->username;
                } else {
                    return AdminUser::findOne($row->admin->pid)->username;
                }
            }],
            'account',
            // 'blocked_account' => ['type' => 'text'],
            'profit_account',
            'loss_account',
            'created_at',
            'login_time',
            'state' => ['search' => 'select'],
            ['header' => '操作', 'width' => '120px', 'value' => function ($row) {
                if ($row['state'] == User::STATE_VALID) {
                    $deleteBtn = Hui::dangerBtn('冻结', ['deleteUser', 'id' => $row->id], ['class' => 'deleteBtn']);
                } else {
                    $deleteBtn = Hui::successBtn('恢复', ['deleteUser', 'id' => $row->id], ['class' => 'deleteBtn']);
                }
                return implode(str_repeat('&nbsp;', 2), [
                    Hui::primaryBtn('修改密码', ['editUserPass', 'id' => $row->id], ['class' => 'editBtn']),
                    $deleteBtn
                ]);
            }],
	    ['type' => ['delete' => 'ajaxDeleteUserPass']]
        ], [
            'searchColumns' => [
                'id',
                'nickname',
                'mobile',
                'parent.nickname' => ['header' => '推荐人'],
                //'admin.username' => ['header' => '用户上级'],
                'ringname' => ['header' => '微圈'],
                'membername' => ['header' => '微会员'],
                'start_time' => ['type' => 'datepicker']
            ]
        ]);

        // 会员总数，总手数，总余额
        $count = User::find()->manager()->count();
        $hand = Order::find()->joinWith(['user'])->manager()->select('SUM(hand) hand')->one()->hand ?: 0;
        $amount = User::find()->manager()->select('SUM(account) account')->one()->account ?: 0;

        return $this->render('list', compact('html', 'count', 'hand', 'amount'));
    }

    /**
     * @authname 删除会员
     */
    public function actionAjaxDeleteUserPass()
    {
        $give = User::findOne(post('id'))->delete();

        if ($give) {
            return self::success('删除成功！');
        } else {
            return self::error('删除失败！');
        }
    }

    /**
     * @authname 用户信息导出
     */
    public function actionUserExcel()
    {
        ini_set("memory_limit", "10000M");
        set_time_limit(0);
        require Yii::getAlias('@vendor/PHPExcel/Classes/PHPExcel.php');
        //获取数据
        $query = (new User)->listQuery()->manager();
        // $count = (new User)->listQuery()->manager()->count();
        $data = $query->all();

        $n = 3;
        //加载PHPExcel插件
        $Excel = new \PHPExcel();
        $Excel->setActiveSheetIndex(0);
        //编辑表格    标题
        $Excel->setActiveSheetIndex(0)->mergeCells('A1:G1');
        $Excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $Excel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setSize(20);
        $Excel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setName('黑体');
        $Excel->getActiveSheet()->setCellValue('A1',config('web_name').'-用户信息统计表');
        //表头
        $Excel->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
        $Excel->setActiveSheetIndex(0)->getStyle('A2:G2')->getFont()->setBold(true);
        $Excel->setActiveSheetIndex(0)->setCellValue('A2','用户的ID');
        $Excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $Excel->setActiveSheetIndex(0)->setCellValue('B2','昵称');
        $Excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $Excel->setActiveSheetIndex(0)->setCellValue('C2','手机号');
        $Excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);

        $filePath = Yii::getAlias('@webroot' . config('uploadPath') . '/excel/U' . u()->id . '/');
        FileHelper::mkdir($filePath);
        $num = 1;
        $m = 1;
        //内容
        foreach ($data as $val) {
            $Excel->setActiveSheetIndex(0)->setCellValue('A'.$n, $val->id);
            if (strpos($val->nickname, '=') === 0){
                $val->nickname = "nanqe" . $val->nickname;
            }
            $Excel->setActiveSheetIndex(0)->setCellValue('B'.$n, $val->nickname);
            $Excel->setActiveSheetIndex(0)->setCellValue('C'.$n, $val->mobile);
            $n++;
            $Excel->getActiveSheet()->getRowDimension($n+1)->setRowHeight(18);
            if ($m != 0 && $m % 1000 == 0) {
                //保存到服务器
                $filename = $filePath . $num . '.xls';
                $fp = fopen($filename, 'w+'); 
                if (!is_writable($filename) ){ 
                    die('文件:' . $filename . '不可写，请检查！'); 
                } 
                $objWriter= \PHPExcel_IOFactory::createWriter($Excel,'Excel5');
                $objWriter->save($filename);
                fclose($fp);
                $num++;
                $n = 3;
                $Excel = new \PHPExcel();
                $Excel->setActiveSheetIndex(0);
                //编辑表格    标题
                $Excel->setActiveSheetIndex(0)->mergeCells('A1:G1');
                $Excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $Excel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setSize(20);
                $Excel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setName('黑体');
                $Excel->getActiveSheet()->setCellValue('A1',config('web_name').'-用户信息统计表');
                //表头
                $Excel->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
                $Excel->setActiveSheetIndex(0)->getStyle('A2:G2')->getFont()->setBold(true);
                $Excel->setActiveSheetIndex(0)->setCellValue('A2','用户的ID');
                $Excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
                $Excel->setActiveSheetIndex(0)->setCellValue('B2','昵称');
                $Excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $Excel->setActiveSheetIndex(0)->setCellValue('C2','手机号');
                $Excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
            }
            $m++;
        }
        $filename = $filePath . $num . '.xls';
        $fp = fopen($filename, 'w+'); 
        if (!is_writable($filename)) { 
            die('文件:' . $filename . '不可写，请检查！'); 
        } 
        $objWriter= \PHPExcel_IOFactory::createWriter($Excel, 'Excel5');
        $objWriter->save($filename);
        fclose($fp);
        //压缩下载
        require Yii::getAlias('@vendor/PHPZip/PHPZip.php');
        $filePath = Yii::getAlias('@webroot' . config('uploadPath') . '/excel/U' . u()->id . '/');
        $archive = new \PHPZip();
        $archive->ZipAndDownload($filePath, config('web_name').'-用户信息统计表');
        deleteDir($filePath);
        //统计
        // $Excel->setActiveSheetIndex(0)->mergeCells('A'.$n.':G'.$n);
        // $Excel->getActiveSheet()->setCellValue('A'.$n,'统计'.$count.'人');
        // $Excel->setActiveSheetIndex(0)->getStyle('A'.$n)->getFont()->setBold(true);
        // //格式
        // $Excel->getActiveSheet()->getStyle('A2:G'.$n)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        // //导出表格
        // header('Content-Type: application/vnd.ms-excel');
        // header('Content-Disposition:attachment;filename="'.config('web_name').'-用户信息统计表.xls');
        // header('Cache-Control: max-age=0');
        // $objWriter= \PHPExcel_IOFactory::createWriter($Excel,'Excel5');
        // $objWriter->save('php://output');
    }

    /**
     * @authname 用户出金导出
     */
    public function actionUserWithExcel()
    {
        ini_set("memory_limit", "10000M");
        set_time_limit(0);
        require Yii::getAlias('@vendor/PHPExcel/Classes/PHPExcel.php');
        //获取数据
        $query = (new UserWithdraw)->listQuery()->manager();
        // $count = (new User)->listQuery()->manager()->count();
        $data = $query->all();

        $n = 3;
        //加载PHPExcel插件
        $Excel = new \PHPExcel();
        $Excel->setActiveSheetIndex(0);
        //编辑表格    标题
        $Excel->setActiveSheetIndex(0)->mergeCells('A1:G1');
        $Excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $Excel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setSize(20);
        $Excel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setName('黑体');
        $Excel->getActiveSheet()->setCellValue('A1',config('web_name').'-用户出金统计表');
        //表头
        $Excel->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
        $Excel->setActiveSheetIndex(0)->getStyle('A2:G2')->getFont()->setBold(true);
        $Excel->setActiveSheetIndex(0)->setCellValue('A2','用户的ID');
        $Excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $Excel->setActiveSheetIndex(0)->setCellValue('B2','昵称');
        $Excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $Excel->setActiveSheetIndex(0)->setCellValue('C2','手机号');
        $Excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $Excel->setActiveSheetIndex(0)->setCellValue('D2','账户余额');
        $Excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);        
        $Excel->setActiveSheetIndex(0)->setCellValue('E2','出金金额');
        $Excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);        
        $Excel->setActiveSheetIndex(0)->setCellValue('F2','审核时间');
        $Excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);        
        $Excel->setActiveSheetIndex(0)->setCellValue('G2','申请状态');
        $Excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
        $filePath = Yii::getAlias('@webroot' . config('uploadPath') . '/excel/U' . u()->id . '/');
        FileHelper::mkdir($filePath);
        $num = 1;
        $m = 1;
        //内容
        foreach ($data as $val) {
            $Excel->setActiveSheetIndex(0)->setCellValue('A'.$n, $val->user->id);
            if (strpos($val->user->nickname, '=') === 0){
                $val->user->nickname = "nanqe" . $val->user->nickname;
            }
            $Excel->setActiveSheetIndex(0)->setCellValue('B'.$n, $val->user->nickname);
            $Excel->setActiveSheetIndex(0)->setCellValue('C'.$n, $val->user->mobile);
            $Excel->setActiveSheetIndex(0)->setCellValue('D'.$n, $val->user->account);
            $Excel->setActiveSheetIndex(0)->setCellValue('E'.$n, $val->amount);
            $Excel->setActiveSheetIndex(0)->setCellValue('F'.$n, $val->updated_at);
            $Excel->setActiveSheetIndex(0)->setCellValue('G'.$n, $val->getOpStateValue($val->op_state));
            $n++;
            $Excel->getActiveSheet()->getRowDimension($n+1)->setRowHeight(18);
            if ($m != 0 && $m % 1000 == 0) {
                //保存到服务器
                $filename = $filePath . $num . '.xls';
                $fp = fopen($filename, 'w+'); 
                if (!is_writable($filename) ){ 
                    die('文件:' . $filename . '不可写，请检查！'); 
                } 
                $objWriter= \PHPExcel_IOFactory::createWriter($Excel,'Excel5');
                $objWriter->save($filename);
                fclose($fp);
                $num++;
                $n = 3;
                $Excel = new \PHPExcel();
                $Excel->setActiveSheetIndex(0);
                //编辑表格    标题
                $Excel->setActiveSheetIndex(0)->mergeCells('A1:G1');
                $Excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $Excel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setSize(20);
                $Excel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setName('黑体');
                $Excel->getActiveSheet()->setCellValue('A1',config('web_name').'-用户出金统计表');
                //表头
                $Excel->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
                $Excel->setActiveSheetIndex(0)->getStyle('A2:G2')->getFont()->setBold(true);
                $Excel->setActiveSheetIndex(0)->setCellValue('A2','用户的ID');
                $Excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
                $Excel->setActiveSheetIndex(0)->setCellValue('B2','昵称');
                $Excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $Excel->setActiveSheetIndex(0)->setCellValue('C2','手机号');
                $Excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $Excel->setActiveSheetIndex(0)->setCellValue('D2','账户余额');
                $Excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);                
                $Excel->setActiveSheetIndex(0)->setCellValue('E2','出金金额');
                $Excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);                
                $Excel->setActiveSheetIndex(0)->setCellValue('F2','审核时间');
                $Excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);                
                $Excel->setActiveSheetIndex(0)->setCellValue('G2','申请状态');
                $Excel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
            }
            $m++;
        }
        $filename = $filePath . $num . '.xls';
        $fp = fopen($filename, 'w+'); 
        if (!is_writable($filename)) { 
            die('文件:' . $filename . '不可写，请检查！'); 
        } 
        $objWriter= \PHPExcel_IOFactory::createWriter($Excel, 'Excel5');
        $objWriter->save($filename);
        fclose($fp);
        //压缩下载
        require Yii::getAlias('@vendor/PHPZip/PHPZip.php');
        $filePath = Yii::getAlias('@webroot' . config('uploadPath') . '/excel/U' . u()->id . '/');
        $archive = new \PHPZip();
        $archive->ZipAndDownload($filePath, config('web_name').'-用户出金统计表');
        deleteDir($filePath);
        //统计
        // $Excel->setActiveSheetIndex(0)->mergeCells('A'.$n.':G'.$n);
        // $Excel->getActiveSheet()->setCellValue('A'.$n,'统计'.$count.'人');
        // $Excel->setActiveSheetIndex(0)->getStyle('A'.$n)->getFont()->setBold(true);
        // //格式
        // $Excel->getActiveSheet()->getStyle('A2:G'.$n)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        // //导出表格
        // header('Content-Type: application/vnd.ms-excel');
        // header('Content-Disposition:attachment;filename="'.config('web_name').'-用户信息统计表.xls');
        // header('Cache-Control: max-age=0');
        // $objWriter= \PHPExcel_IOFactory::createWriter($Excel,'Excel5');
        // $objWriter->save('php://output');
    }

    /**
     * @authname 用户充值导出
     */
    public function actionUserChargeExcel()
    {
        ini_set("memory_limit", "10000M");
        set_time_limit(0);
        require Yii::getAlias('@vendor/PHPExcel/Classes/PHPExcel.php');
        //获取数据
        $query = (new UserCharge)->listQuery()->manager();
        // $count = (new User)->listQuery()->manager()->count();
        $data = $query->all();

        $n = 3;
        //加载PHPExcel插件
        $Excel = new \PHPExcel();
        $Excel->setActiveSheetIndex(0);
        //编辑表格    标题
        $Excel->setActiveSheetIndex(0)->mergeCells('A1:G1');
        $Excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $Excel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setSize(20);
        $Excel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setName('黑体');
        $Excel->getActiveSheet()->setCellValue('A1',config('web_name').'-用户充值信息统计表');
        //表头
        $Excel->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
        $Excel->setActiveSheetIndex(0)->getStyle('A2:G2')->getFont()->setBold(true);
        $Excel->setActiveSheetIndex(0)->setCellValue('A2','ID');
        $Excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
        $Excel->setActiveSheetIndex(0)->setCellValue('B2','昵称');
        $Excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $Excel->setActiveSheetIndex(0)->setCellValue('C2','手机号');
        $Excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $Excel->setActiveSheetIndex(0)->setCellValue('D2','充值金额');
        $Excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);        
        $Excel->setActiveSheetIndex(0)->setCellValue('E2','支付方式');
        $Excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $Excel->setActiveSheetIndex(0)->setCellValue('F2','充值时间');
        $Excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);        


        $filePath = Yii::getAlias('@webroot' . config('uploadPath') . '/excel/U' . u()->id . '/');
        FileHelper::mkdir($filePath);
        $num = 1;
        $m = 1;
        //内容
        foreach ($data as $val) {
            $Excel->setActiveSheetIndex(0)->setCellValue('A'.$n, $val->id);
            if (strpos($val->user->nickname, '=') === 0){
                $val->user->nickname = "nanqe" . $val->user->nickname;
            }
            $Excel->setActiveSheetIndex(0)->setCellValue('B'.$n, $val->user->nickname);
            $Excel->setActiveSheetIndex(0)->setCellValue('C'.$n, $val->user->mobile);
            $Excel->setActiveSheetIndex(0)->setCellValue('D'.$n, $val->amount);
            $Excel->setActiveSheetIndex(0)->setCellValue('E'.$n, $val->getChargeTypeValue($val->charge_type));
            $Excel->setActiveSheetIndex(0)->setCellValue('F'.$n, $val->created_at);
            $n++;
            $Excel->getActiveSheet()->getRowDimension($n+1)->setRowHeight(18);
            if ($m != 0 && $m % 1000 == 0) {
                //保存到服务器
                $filename = $filePath . $num . '.xls';
                $fp = fopen($filename, 'w+'); 
                if (!is_writable($filename) ){ 
                    die('文件:' . $filename . '不可写，请检查！'); 
                } 
                $objWriter= \PHPExcel_IOFactory::createWriter($Excel,'Excel5');
                $objWriter->save($filename);
                fclose($fp);
                $num++;
                $n = 3;
                $Excel = new \PHPExcel();
                $Excel->setActiveSheetIndex(0);
                //编辑表格    标题
                $Excel->setActiveSheetIndex(0)->mergeCells('A1:G1');
                $Excel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $Excel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setSize(20);
                $Excel->setActiveSheetIndex(0)->getStyle('A1')->getFont()->setName('黑体');
                $Excel->getActiveSheet()->setCellValue('A1',config('web_name').'-用户充值信息统计表');
                //表头
                $Excel->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00);
                $Excel->setActiveSheetIndex(0)->getStyle('A2:G2')->getFont()->setBold(true);
                $Excel->setActiveSheetIndex(0)->setCellValue('A2','ID');
                $Excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
                $Excel->setActiveSheetIndex(0)->setCellValue('B2','昵称');
                $Excel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
                $Excel->setActiveSheetIndex(0)->setCellValue('C2','手机号');
                $Excel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
                $Excel->setActiveSheetIndex(0)->setCellValue('D2','充值金额');
                $Excel->getActiveSheet()->getColumnDimension('D')->setWidth(25);        
                $Excel->setActiveSheetIndex(0)->setCellValue('E2','支付方式');
                $Excel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
                $Excel->setActiveSheetIndex(0)->setCellValue('F2','充值时间');
                $Excel->getActiveSheet()->getColumnDimension('F')->setWidth(25);  
            }
            $m++;
        }
        $filename = $filePath . $num . '.xls';
        $fp = fopen($filename, 'w+'); 
        if (!is_writable($filename)) { 
            die('文件:' . $filename . '不可写，请检查！'); 
        } 
        $objWriter= \PHPExcel_IOFactory::createWriter($Excel, 'Excel5');
        $objWriter->save($filename);
        fclose($fp);
        //压缩下载
        require Yii::getAlias('@vendor/PHPZip/PHPZip.php');
        $filePath = Yii::getAlias('@webroot' . config('uploadPath') . '/excel/U' . u()->id . '/');
        $archive = new \PHPZip();
        $archive->ZipAndDownload($filePath, config('web_name').'-用户充值信息统计表');
        deleteDir($filePath);
        //统计
        // $Excel->setActiveSheetIndex(0)->mergeCells('A'.$n.':G'.$n);
        // $Excel->getActiveSheet()->setCellValue('A'.$n,'统计'.$count.'人');
        // $Excel->setActiveSheetIndex(0)->getStyle('A'.$n)->getFont()->setBold(true);
        // //格式
        // $Excel->getActiveSheet()->getStyle('A2:G'.$n)->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);
        // //导出表格
        // header('Content-Type: application/vnd.ms-excel');
        // header('Content-Disposition:attachment;filename="'.config('web_name').'-用户信息统计表.xls');
        // header('Cache-Control: max-age=0');
        // $objWriter= \PHPExcel_IOFactory::createWriter($Excel,'Excel5');
        // $objWriter->save('php://output');
    }

     /* 导出excel函数*/  
    public function actionPush()  
    {
        $name='Excel';
        ini_set("memory_limit", "10000M");
        set_time_limit(0);
        // ob_start();
        require Yii::getAlias('@vendor/PHPExcel/Classes/PHPExcel.php');
        error_reporting(E_ALL);  
        date_default_timezone_set('Europe/London'); 

        $objPHPExcel = new \PHPExcel(); 
        // $query = (new User)->listQuery()->manager()->andWhere('user.id > 102200'); 
        $query = (new User)->listQuery()->manager();
        $data = $query->all();

        /*以下是一些设置 ，什么作者  标题啊之类的*/  
         $objPHPExcel->getProperties()->setCreator("转弯的阳光")  
           ->setLastModifiedBy("转弯的阳光")  
           ->setTitle("数据EXCEL导出")  
           ->setSubject("数据EXCEL导出")  
           ->setDescription("备份数据")  
           ->setKeywords("excel")  
          ->setCategory("result file");  
         /*以下就是对处理Excel里的数据， 横着取数据，主要是这一步，其他基本都不要改*/  
        foreach($data as $k => $val){  
            $num = $k+1;  
            $objPHPExcel->setActiveSheetIndex(0)//Excel的第A列，uid是你查出数组的键值，下面以此类推  
                      ->setCellValue('A'.$num, $val->id)     
                      ->setCellValue('B'.$num, $val->nickname)  
                      ->setCellValue('C'.$num, $val->mobile);  
        }  
  
        $objPHPExcel->getActiveSheet()->setTitle('User');  
        $objPHPExcel->setActiveSheetIndex(0);  
        header('Content-Type: applicationnd.ms-excel');  
        header('Content-Disposition: attachment;filename="'.$name.'.xls"');  
        header('Cache-Control: max-age=0');  
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  
        $objWriter->save('php://output');  
        exit;  
      } 

    /**
     * @authname 修改会员密码
     */
    public function actionEditUserPass() 
    {
        $user = User::findModel(get('id'));
        $user->password = post('password');
        if ($user->validate()) {
            $user->hashPassword()->update(false);
            return success();
        } else {
            return error($user);
        }
    }

    /**
     * @authname 冻结/恢复用户
     */
    public function actionDeleteUser() 
    {
        $user = User::find()->where(['id' => get('id')])->one();

        if ($user->toggle('state')) {
            return success('冻结成功！');
        } else {
            return success('账号恢复成功！');
        }
    }

    /**
     * @authname 赠送优惠券
     */
    public function actionSendCoupon()
    {
        $ids = post('ids');

        // 送给所有人
        if (!$ids) {
            $ids = User::find()->map('id', 'id');
        }
        UserCoupon::sendCoupon($ids, post('coupon_id'), post('number') ?: 1);
        return success('赠送成功');
    }

    /**
     * @authname 会员持仓列表
     */
    public function actionPositionList()
    {
        $query = (new User)->listQuery()->andWhere(['user.state' => User::STATE_VALID])->manager();

        $order = [];
        $html = $query->getTable([
            'id',
            'nickname' => ['type' => 'text'],
            'mobile',
            ['header' => '盈亏', 'value' => function ($row) use (&$order) {
                $order = Order::find()->where(['user_id' => $row['id'], 'order_state' => Order::ORDER_POSITION])->select(['SUM(hand) hand', 'SUM(profit) profit'])->one();
                if ($order->profit == null) {
                    return '无持仓';
                } elseif ($order->profit >= 0) {
                    return Html::redSpan($order->profit);
                } else {
                    return Html::greenSpan($order->profit);
                }
            }],
            ['header' => '持仓手数', 'value' => function ($row) use (&$order) {
                if ($order->hand == null) {
                    return '无持仓';
                } else {
                    return $order->hand;
                }
            }],
            'account',
            'state'
        ], [
            'searchColumns' => [
                'nickname',
                'mobile',
                'created_at' => ['type' => 'date']
            ]
        ]);

        return $this->render('positionList', compact('html'));
    }

    /**
     * @authname 会员赠金
     */
    public function actionGiveList()
    {
        if (req()->isPost) {
            $user = User::findModel(get('id'));
            $amount = post('amount');
            if (!is_numeric($amount)) {
                return error('非法参数！');
            }
            $user->account += post('amount');        
            if ($user->update()) {
                $userGive = new UserGive();
                $userGive->user_id = $user->id;
                $userGive->amount = $amount;
                $userGive->save();
                return success();
            } else {
                return error($user);
            }
        }

        $query = (new User)->listQuery()->andWhere(['user.state' => User::STATE_VALID]);

        $html = $query->getTable([
            'id',
            'nickname',
            'mobile',
            'account',
            'admin.username' => ['header' => '用户上级'],
            ['header' => '操作', 'width' => '40px', 'value' => function ($row) {
                return Hui::primaryBtn('赠金', ['', 'id' => $row['id']], ['class' => 'giveBtn']);
            }]
        ], [
            'searchColumns' => [
                'nickname',
                'mobile',
                'admin.username' => ['header' => '用户上级'],
            ]
        ]);

        return $this->render('giveList', compact('html'));
    }

    /**
     * @authname 变更会员经纪人
     */
    public function actionUpdateManager()
    {
        if (req()->isPost) {
            $user = User::findOne(get('id'));
            if (empty($user)) {
                return error('找不到这个用户！');
            }
            $admin_id = post('admin_id');
            if (!empty($admin_id)) {
                $user->admin_id = $admin_id;
                $admin = AdminUser::find()->where(['id' => $user->admin_id, 'power' => AdminUser::POWER_RING])->one();
                if (empty($admin)) {
                    return error('找不到这个微圈用户ID！');
                }
            } else {
                $user->pid = post('pid');
                if (!is_numeric($user->pid)) {
                    return error('非法参数！');
                }
                $puser = User::find()->where(['id' => $user->pid, 'is_manager' => 1])->one();
                if (empty($puser)) {
                    return error('找不到这个经纪人！');
                }
            }
            if ($user->update()) {
                return success();
            } else {
                return error($user);
            }
        }

        $query = (new User)->listQuery()->andWhere(['user.state' => User::STATE_VALID])->manager();

        $html = $query->getTable([
            'id',
            'nickname',
            'mobile',
            'pid' => ['header' => '经纪人ID'],
            'admin.username' => ['header' => '用户上级'],
            ['header' => '操作', 'width' => '40px', 'value' => function ($row) {
                return Hui::primaryBtn('变更经纪人', ['', 'id' => $row['id']], ['class' => 'giveBtn']).Hui::primaryBtn('变更微圈id', ['', 'id' => $row['id']], ['class' => 'ringBtn']);
            }]
        ], [
            'searchColumns' => [
                'nickname',
                'mobile',
                'pid' => ['header' => '经纪人ID'],
                'admin.username' => ['header' => '用户上级'],
            ]
        ]);

        return $this->render('updateManager', compact('html'));
    }

    /**
     * @authname 赠金记录
     */
    public function actionGiveMoneyList()
    {
        $query = (new UserGive)->giveListQuery()->andWhere(['user.state' => User::STATE_VALID]);
        $amount = (new UserGive)->giveListQuery()->andWhere(['user.state' => User::STATE_VALID])->select('SUM(amount) amount')->one()->amount ?: 0;

        $html = $query->getTable([
            'user.id',
            'user.nickname',
            'user.mobile',
            'amount',
            'created_at' => ['header' => '赠送时间'],
            // 'adminUser.username' => ['header' => '操作人账号'],
            'updated_by' => ['header' => '后台操作人id'],
        ], [
            'ajaxReturn' => [
                'countAmount' => $amount,
            ],
            'searchColumns' => [
                'user.id',
                'user.mobile',
                // 'adminUser.username' => ['header' => '操作人账号'],
                'updated_by' => ['header' => '后台操作人id'],
            ]
        ]);

        return $this->render('giveMoneyList', compact('html', 'amount'));
    }

    /**
     * @authname 保证金充值记录
     */
    public function actionAdminDeposit()
    {
        $query = (new UserCharge)->adminListQuery()->andWhere(['charge_state' => UserCharge::CHARGE_STATE_PASS])->andWhere(['<', 'user_id', 10000]);
        $countQuery = (new UserCharge)->adminListQuery()->andWhere(['charge_state' => UserCharge::CHARGE_STATE_PASS])->andWhere(['<', 'user_id', 10000]);
        $amount = $countQuery->select('SUM(amount) amount')->one()->amount ?: 0;;
        $html = $query->getTable([
            'adminUser.username',
            // 'adminUser.id',
            'amount',
            'created_at',
        ], [
            'searchColumns' => [
                'adminUser.username',
                'time' => ['header' => '充值时间', 'type' => 'dateRange'],
            ]
        ]);

        return $this->render('AdminDeposit', compact('html', 'amount'));
    }

    public function actionSearchMoney()
    {
        if(empty(post('out_sn')) || !is_numeric(post('out_sn'))) {
            return success('订单号非法');
        }
        $userWithdraw = new UserWithdraw;
        $result = $userWithdraw->getMoneyState(post('out_sn'));
        return success($result['resp_msg']);
    }

    /**
     * @authname 会员出金管理
     */
    public function actionWithdrawList()
    {
        //$query = (new UserWithdraw)->listQuery()->joinWith(['user.parent'])->andWhere(['user.state' => User::STATE_VALID])->orderBy('userWithdraw.updated_at DESC');
        $query = (new UserWithdraw)->listQuery()->joinWith(['user.parent'])->orderBy('userWithdraw.updated_at DESC');
        //$countQuery = (new UserWithdraw)->listQuery()->joinWith(['user'])->andWhere(['user.state' => User::STATE_VALID])->andWhere(['op_state' => UserWithdraw::OP_STATE_PASS]);
        $countQuery = (new UserWithdraw)->listQuery()->joinWith(['user'])->andWhere(['op_state' => UserWithdraw::OP_STATE_PASS]);
        $count = $countQuery->select('SUM(amount) amount')->one()->amount ?: 0;

        $html = $query->getTable([
            'out_sn' => ['header' => '出金订单号'],
            'user.id',
            'user.nickname',
            'user.mobile',
            'admin.id' => ['header' => '微圈', 'value' => function ($row) {
                if (!isset($row->user->admin->power)) {
                    return '';
                }
                return $row->user->admin->power == AdminUser::POWER_RING ? $row->user->admin->username : '无';
            }],
            'admin.username' => ['header' => '微会员', 'value' => function ($row) {
                if (!isset($row->user->admin->power)) {
                    return '';
                }
                if ($row->user->admin->power == AdminUser::POWER_MEMBER) {
                    return $row->user->admin->username;
                } else {
                    return AdminUser::findOne($row->user->admin->pid)->username;
                }
            }],
            'amount' => '出金金额',
            'updated_at',
            'op_state' => ['search' => 'select'],
            // 'isgive' => ['search' => 'select', 'header' => '是否回款'],
            'user.account',
            ['header' => '操作', 'width' => '70px', 'value' => function ($row) {
                $state =  Hui::primaryBtn('更改出金状态', ['user/stateWithdraw', 'id' => $row['id']], ['class' => 'layer.iframe']);
                if ($row['op_state'] == UserWithdraw::OP_STATE_WAIT) {
                    return Hui::primaryBtn('会员出金', ['user/verifyWithdraw', 'id' => $row['id']], ['class' => 'layer.iframe']) . $state;
                } else {
                    $string = Html::successSpan('已审核');
                    // if ($row['isgive'] == UserWithdraw::ISGIVE_NO) {
                    //     return $string .= Hui::primaryBtn('是否返还', ['isGiveMoney', 'id' => $row->id], ['class' => 'isGiveMoney']);
                    // }
                }
                return $string .= Hui::primaryBtn('查看详细', ['user/readWithdraw', 'id' => $row['id']], ['class' => 'layer.iframe']);
                // if ($row['op_state'] == UserWithdraw::OP_STATE_WAIT) {
                //     $string = Hui::primaryBtn('会员出金', ['user/verifyWithdraw', 'id' => $row['id']], ['class' => 'layer.iframe']);
                // } elseif ($row['op_state'] == UserWithdraw::OP_STATE_MID) {
                //     $string = Html::successSpan('审核中');
                // } else {
                //     $string = Html::successSpan('已审核');
                // }
                // return $string .= Hui::primaryBtn('查看详细详细', ['user/readWithdraw', 'id' => $row['id']], ['class' => 'layer.iframe']);
            }]
        ], [
            'searchColumns' => [
                'out_sn' => ['header' => '出金订单号'],
                'user.id' => ['header' => '用户id'],
                'user.nickname',
                'user.mobile',
                'ringname' => ['header' => '微圈'],
                'membername' => ['header' => '微会员'],
                'time' => ['header' => '审核时间', 'type' => 'dateRange']
            ],
            'ajaxReturn' => [
                'count' => $count
            ]
        ]);
        

        return $this->render('withdrawList', compact('html', 'count'));
            $model->update();
            return success();
    }
    /**
     * @authname 更改出金状态
     */
    public function actionStateWithdraw($id)
    {
        $model = UserWithdraw::find()->where(['id' => $id])->one();

        if (req()->isPost) {
            $model->op_state = post('UserWithdraw')['op_state'];
            if($model->update()) {
                return success();
            }else {
                return error($model);
            }
        }

        return $this->render('stateWithdraw', compact('model'));
    }
    /**
     * @authname 是否返还用户出金的钱
     */
    public function actionIsGiveMoney() 
    {
        $model = UserWithdraw::find()->with('user.userAccount')->where(['isgive' => UserWithdraw::ISGIVE_NO, 'id' => get('id')])->one();
        $content = post('content');
        if ($content == '是') {
            $model->user->account += $model->amount + config('web_out_money_fee', 5);
            if ($model->user->update()) {
                $model->isgive = UserWithdraw::ISGIVE_YES;
                $model->update();
                return success();
            } else {
                return error('修改失败！');
            }
        } else if ($content == '否') {
            $model->op_state = UserWithdraw::OP_STATE_PASS;
            $model->isgive = UserWithdraw::ISGIVE_YES;
        } else {
            return error('参数非法！');
        }
    }

    /**
     * @authname 是否返还用户出金的钱（后台）
     */
    public function actionAdminIsGiveMoney() 
    {
        $model = RetailWithdraw::find()->with('adminAccount', 'retail')->where(['isgive' => RetailWithdraw::ISGIVE_NO, 'id' => get('id')])->one();
        $content = post('content');
        if ($content == '是') {
            $model->retail->total_fee += $model->amount + config('web_out_money_fee', 5);
            if ($model->retail->update()) {
                $model->isgive = RetailWithdraw::AISGIVE_YES;
                $model->update();
                return success();
            } else {
                return error('修改失败！');
            }
        } else if ($content == '否') {
            $model->state = RetailWithdraw::STATE_PASS;
            $model->isgive = RetailWithdraw::AISGIVE_YES;
        } else {
            return error('参数非法！');
        }
    }

    /**
     * @authname 后台用户申请出金列表
     */
    public function actionAdminWithdraw()
    {
        $query = Retail::find()->where(['admin_id' => u()->id]);
        $html = $query->getTable([
            'total_fee',
            ['header' => '操作', 'width' => '70px', 'value' => function ($row) {
                return $string = Hui::successBtn('提现', ['user/adminVerifyWithdraw'], ['class' => 'fancybox fancybox.iframe']);
            }]
        ]);
        

        return $this->render('adminWithdraw', compact('html'));
    }

    /**
     * @authname 后台用户出金申请操作
     */
    public function actionAdminVerifyWithdraw()
    {
        $adminAccount = AdminAccount::find()->where(['admin_id' => u()->id])->one();
        if(empty($adminAccount)) {
            $adminAccount = new AdminAccount;
        }
        $retailWithdraw = new RetailWithdraw;
        $adminAccount->scenario = 'with';
        $retailWithdraw->scenario = 'retailWith';
        if (req()->isPost) {
            if(date('w') == 0 || date('w') == 6 || date('G') < 9 || date('G') > 17) {
                return error('不在规定提现时间内');
            }
            $retail = Retail::find()->where(['admin_id' => u()->id])->one();
            if($adminAccount->load(post()) && $retailWithdraw->load(post())) {
                if(u()->id >= AdminUser::POWER_OPERATE) {
                    $fee = 10;
                }else {
                    $fee = 5;
                }
                if($retailWithdraw->amount + $fee > $retail->total_fee || $retailWithdraw->amount <= 0) {
                    return error('余额不足以出金');
                }
                $adminAccount->admin_id = u()->id;
                $bank = Bank::find()->where(['number' => $adminAccount->bank_name])->one();
                if (empty($bank)) {
                   return error('找不到这个银行名称！'); 
                }
                $adminAccount->bank_code = $bank->name;
                if($adminAccount->validate()) {
                    if($adminAccount->id) {
                        $adminAccount->update();
                    }else {
                        $adminAccount->insert(false);
                    }
                    if($retailWithdraw->validate()) {
                        $retailWithdraw->admin_id = u()->id;
                        $retailWithdraw->state = RetailWithdraw::STATE_WAIT;
                        $retailWithdraw->out_sn = u()->id . date("YmdHis") . rand(1000, 9999);;
                        if ($retailWithdraw->insert(false)) {
                                $retail->total_fee -= $retailWithdraw->amount + $fee;
                                $retail->update();    
                                return success();
                        } else {
                            return error($model);
                        }
                    }else {
                        return error($retailWithdraw);
                    }
                }else {
                    return error($adminAccount);
                }
            }
        }

        return $this->render('adminVerifyWithdraw', compact('adminAccount', 'retailWithdraw'));
    }

    /**
     * @authname 超管审核出金管理
     */
    public function actionAdminsWithdrawList()
    {
        $query = (new RetailWithdraw)->listQuery()->andWhere(['type' => RetailWithdraw::TYPE_FEE])->orderBy('retailWithdraw.state DESC');
        $countQuery = (new RetailWithdraw)->listQuery()->joinWith(['adminUser'])->andWhere(['retailWithdraw.state' => RetailWithdraw::STATE_PASS]);
        $count = $countQuery->select('SUM(amount) amount')->one()->amount ?: 0;

        $html = $query->getTable([
            'out_sn',
            'retail.company_name',
            'amount' => '出金金额',
            'created_at',
            'state' => ['header' => '申请状态', 'search' => 'select'],
            ['header' => '操作', 'width' => '70px', 'value' => function ($row) {
                $state =  Hui::primaryBtn('更改出金状态', ['user/adminStateWithdraw', 'id' => $row['id']], ['class' => 'layer.iframe']);
                if ($row['state'] == RetailWithdraw::STATE_WAIT) {
                    return  Hui::primaryBtn('会员出金', ['user/adminsVerifyWithdraw', 'id' => $row['id']], ['class' => 'layer.iframe']) . $state;
                } else {
                    $string = Html::successSpan('已审核');
                }

                return $string .= Hui::primaryBtn('查看详细', ['user/adminsReadWithdraw', 'id' => $row['id']], ['class' => 'layer.iframe']);
            }]
        ], [
            'searchColumns' => [
                'retail.company_name',
                'time' => ['header' => '审核时间', 'type' => 'dateRange'],
                'out_sn'
            ],
            'ajaxReturn' => [
                'count' => $count
            ]
        ]);
        

        return $this->render('adminsWithdrawList', compact('html', 'count'));
            $model->update();
            return success();
    }

    /**
     * @authname 更改出金状态
     */
    public function actionAdminStateWithdraw($id)
    {
        $model = RetailWithdraw::find()->where(['id' => $id])->one();

        if (req()->isPost) {
            $model->state = post('RetailWithdraw')['state'];
            if($model->update()) {
                return success();
            }else {
                return error($model);
            }
        }

        return $this->render('adminStateWithdraw', compact('model'));
    }

    /**
     * @authname 超管审核出金操作
     */
    public function actionAdminsVerifyWithdraw($id)
    {
        $model = RetailWithdraw::find()->with('adminAccount', 'retail')->where(['id' => $id])->one();
        if (req()->isPost) {
            // test(1);
            $model->state = post('state');
            if ($model->state == RetailWithdraw::STATE_PASS) {
                $info = $model->newoutAdminMoney();
                if ($info['resp_code'] != '0000') {
                    return error($info['resp_msg']);
                }
            }
            if ($model->update()) {
                if ($model->state == RetailWithdraw::STATE_DENY) {
                    $model->retail->total_fee += $model->amount + config('web_out_money_fee', 5);
                    $model->retail->update();    
                }
                return success();
            } else {
                return error($model);
            }
        }

        return $this->render('adminsVerifyWithdraw', compact('model'));
    }

    /**
     * @authname 查看后台会员出金详细
     */
    public function actionAdminsReadWithdraw($id)
    {
        $model = RetailWithdraw::find()->with('adminAccount', 'retail')->where(['id' => $id])->one();

        return $this->render('adminsReadWithdraw', compact('model'));
    }

    /**
     * @authname 会员出金操作
     */
    public function actionVerifyWithdraw($id)
    {
        $model = UserWithdraw::find()->with('user.userAccount')->where(['id' => $id])->one();

        if (req()->isPost) {
            $model->op_state = post('state');
            /*if ($model->op_state == UserWithdraw::OP_STATE_PASS) {
                $info = $model->easyPayOutUserMoney();
                if (!$info || $info['']) {
                    return error($info['resp_msg']);
                }
            }*/
            if ($model->update()) {
                if ($model->op_state == UserWithdraw::OP_STATE_DENY) {
                    $model->user->account += $model->amount + config('web_out_money_fee', 2);
                    $model->user->update();    
                }
                return success();
            } else {
                return error($model);
            }
        }

        return $this->render('verifyWithdraw', compact('model'));
        // $model = UserWithdraw::find()->with('user.userAccount')->where(['id' => $id])->one();

        // if (req()->isPost) {
        //     $model->op_state = post('state');
        //     if ($model->op_state == UserWithdraw::OP_STATE_PASS) {
        //         $info = $model->outUserMoney();
                
        //         if ($info['respcd'] != '0000') {
        //             return error($info['respmsg']);
        //         } else {
        //             switch ($info['data']['cash_status']) {
        //                 case '11':  //代付成功
        //                     $model->op_state = UserWithdraw::OP_STATE_PASS;
        //                     break;

        //                 case '12':  //代付失败
        //                     $model->op_state = UserWithdraw::OP_STATE_DENY;
        //                     break;
                        
        //                 default: //9：开始代付；10：代付中
        //                     $model->op_state = UserWithdraw::OP_STATE_MID;
        //                     break;
        //             }
        //         }
        //     }
        //     if ($model->update()) {
        //         if ($model->op_state == UserWithdraw::OP_STATE_DENY) {
        //             $model->user->account += $model->amount + config('web_out_money_fee', 5);
        //             $model->user->update();    
        //         }
        //         return success();
        //     } else {
        //         return error($model);
        //     }
        // }

        // return $this->render('verifyWithdraw', compact('model'));
    }

    /**
     * @authname 查看会员出金详细
     */
    public function actionReadWithdraw($id)
    {
        $model = UserWithdraw::find()->with('user.userAccount')->where(['id' => $id])->one();

        return $this->render('readWithdraw', compact('model'));
    }
    
    /**
     * @authname 会员充值记录
     */
    public function actionChargeRecordList()
    {
        $query = (new UserCharge)->listQuery()->joinWith(['user.parent'])->manager()->orderBy('userCharge.id DESC');
        $countQuery = (new UserCharge)->listQuery()->joinWith(['user'])->manager();
        $count = $countQuery->select('SUM(amount) amount')->one()->amount ?: 0;

        $html = $query->getTable([
            'user.id',
            'user.nickname' => '充值人',
            'user.mobile',
            'admin.id' => ['header' => '微圈', 'value' => function ($row) {
                if (!isset($row->user->admin->power)) {
                    return '';
                }
                return $row->user->admin->power == AdminUser::POWER_RING ? $row->user->admin->username : '无';
            }],
            'admin.username' => ['header' => '微会员', 'value' => function ($row) {
                if (!isset($row->user->admin->power)) {
                    return '';
                }
                if ($row->user->admin->power == AdminUser::POWER_MEMBER) {
                    return $row->user->admin->username;
                } else {
                    return AdminUser::findOne($row->user->admin->pid)->username;
                }
            }],
            'amount',
            'actual',
            'poundage',
            ['header' => '推荐人', 'value' => function ($row) {
                return $row->user->getParentLink('user.id');
            }],
            'user.account',
            'charge_type',
            'created_at'
        ], [
            'searchColumns' => [
                'user.id' => ['header' => '用户id'],
                'user.nickname',
                'user.mobile',
                'ringname' => ['header' => '微圈'],
                'membername' => ['header' => '微会员'],
                'time' => ['header' => '充值时间', 'type' => 'dateRange'],
            ],
            'ajaxReturn' => [
                'count' => $count
            ]
        ]);

        return $this->render('chargeRecordList', compact('html', 'count'));
    }

    /**
     * @authname 审核经纪人
     */
    public function actionVerifyManager()
    {
        if (req()->isPost) {
            $model = User::findModel(get('id'));
            $model->apply_state = get('apply_state');
            if ($model->apply_state == User::APPLY_STATE_PASS) {
                $model->is_manager = User::IS_MANAGER_YES;
                $userExtend = UserExtend::findOne($model->id);
                $model->admin_id = $userExtend->coding;
                $managerUser = User::find()->where(['is_manager' => User::IS_MANAGER_YES, 'member_id' => $model->member_id])->orderBy('manager_id DESC')->one();
                $model->manager_id = 1;
                if (!empty($managerUser)) {
                    $model->manager_id = $managerUser->manager_id + 1;
                }
            }
            if ($model->update()) {
                return success();
            } else {
                return error($model);
            }
        }

        $query = (new User)->listQuery()->joinWith(['userExtend'])->andWhere(['user.apply_state' => User::APPLY_STATE_WAIT, 'user.state' => User::STATE_VALID]);
        if (u()->power < AdminUser::POWER_ADMIN) {
            if (u()->power == AdminUser::POWER_RING) {
                $query = $query->andWhere(['userExtend.coding' => u()->id]);
            } else {
                $idArr = [u()->id];
                $arr = [];
                $bool = true;
                do {
                    $idArr = AdminUser::find()->where(['in', 'pid', $idArr])->map('id', 'id');
                    if (empty($idArr)) {
                        $bool = false;
                    } else {
                        $arr = $idArr;
                    }
                } while ($bool);
                $query = $query->andWhere(['in', 'userExtend.coding', $arr]); 
            }
        }

        $html = $query->getTable([
            'id',
            'userExtend.realname' => ['header' => '申请真实姓名'],
            'userExtend.mobile' => ['header' => '申请手机号'],
            'userExtend.created_at' => ['header' => '申请时间'],
            'nickname',
            'mobile' => ['header' => '注册手机号'],
            // 'pid' => ['header' => '推荐人', 'value' => function ($row) {
            //     return $row->getParentLink();
            // }],
            'created_at',
            ['type' => [], 'value' => function ($row) {
                return implode(str_repeat('&nbsp;', 2), [
                    Hui::primaryBtn('审核通过', ['', 'id' => $row->id, 'apply_state' => User::APPLY_STATE_PASS], ['class' => 'verifyBtn']),
                    Hui::dangerBtn('不通过', ['', 'id' => $row->id, 'apply_state' => User::APPLY_STATE_DENY], ['class' => 'verifyBtn'])
                ]);
            }]
        ], [
            'searchColumns' => [
                'id',
                'nickname',
                'mobile'
            ]
        ]);

        return $this->render('verifyManager', compact('html'));
    }

    /**
     * @authname 经纪人返点记录列表
     */
    public function actionManagerRebateList()
    {
        $query = (new UserRebate)->managerListQuery()->orderBy('userRebate.created_at DESC');
        $count = $query->sum('amount') ?: 0;

        $html = $query->getTable([
            'id',
            'parent.nickname' => ['header' => '获得返点用户'],
            'user.nickname' => ['header' => '会员昵称（手机号）', 'value' => function ($row) {
                return Html::a($row->user->nickname . "({$row->user->mobile})", ['', 'search[user.id]' => $row->user->id], ['class' => 'parentLink']);
            }],
            'amount',
            'point' => function ($row) {
                return $row->point . '%';
            },
            'created_at' => '返点时间'
        ], [
            'searchColumns' => [
                'parent.id' => ['header' => '会员ID'],
                'parent.nickname' => ['header' => '返点用户昵称'],
                'parent.mobile' => ['header' => '经纪人手机号'],
                'time' => 'timeRange'
            ],
            'ajaxReturn' => [
                'count' => $count
            ]
        ]);

        return $this->render('rebateList', compact('html', 'count'));
    }

    /**
     * @authname 管理员返点记录列表
     */
    public function actionAdminRebateList()
    {
        $query = (new UserRebate)->adminListQuery()->orderBy('userRebate.created_at DESC');
        $count = $query->sum('amount') ?: 0;

        $html = $query->getTable([
            'id',
            'adminUser.username' => ['header' => '获得返点账号'],
            'adminUser.mobile',
            'user.nickname' => ['header' => '会员昵称（手机号）', 'value' => function ($row) {
                return Html::a($row->user->nickname . "({$row->user->mobile})", ['', 'search[user.id]' => $row->user->id], ['class' => 'parentLink']);
            }],
            'amount',
            'point' => function ($row) {
                return $row->point . '%';
            },
            'created_at' => '返点时间'
        ], [
            'searchColumns' => [
                'adminUser.id' =>  ['header' => '管理员ID'],
                'adminUser.username' =>  ['header' => '返点账号'],
                'adminUser.mobile' => ['header' => '管理员手机号'],
                'time' => 'timeRange'
            ],
            'ajaxReturn' => [
                'count' => $count
            ]
        ]);

        return $this->render('rebateList', compact('html', 'count'));
    }
}
