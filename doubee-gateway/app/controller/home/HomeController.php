<?php

declare(strict_types=1);

namespace app\controller\home;

use app\controller\AbstractController;
use app\kernel\plugin\Plugin;
use app\model\MerchantApplication;
use app\model\SystemLoginLog;
use app\model\SystemUser;
use app\model\MerchantBankCard;
use app\utils\DateUtils;
use app\utils\StringUtils;
use think\annotation\route\Route;
use think\db\Query;
use Symfony\Component\Finder\Finder;

class HomeController extends AbstractController
{
    #[Route("GET", "/")]
    public function index()
    {
//        $model = new MerchantApplication();
//        $model->merchant_id = 1;
//        $model->application_no = StringUtils::generateUniqueAppNumber();
//        $model->secret = StringUtils::generateRandStr(42);
//        $model->name = "默认应用" . $model->application_no;
//        $model->creation_time = DateUtils::current();
//        $model->status = 1;
//        $model->save();

//        $finder = new Finder();
//        $finder->files()->in(app_path('plugin'))->depth(1);;
//
//        $path = [];
//        foreach ($finder as $file) {
//            $path[] = $file->getRealPath(); // 获取完整路径
//        }
//
//        print_r($path);
//        exit;

//        return $this->json();
    }
}