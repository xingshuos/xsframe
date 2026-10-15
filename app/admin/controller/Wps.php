<?php
namespace app\xs_tj\controller;

use think\facade\Request;
use think\facade\View;
use think\facade\Session;
use app\service\WpsService;
use app\admin\model\WpsFile as WpsFileModel;

class WpsFile
{
    
    /**
     * 文档预览/编辑页面
     */
    public function preview()
    {
        /*$file = WpsFileModel::getByFileId($fileId);
        if (!$file) {
            return '文件不存在';
        }
        
        $userId = Session::get('user_id', 1);
        $userName = Session::get('user_name', '用户');
        
        $wpsService = new WpsService();
        $previewUrl = $wpsService->generatePreviewUrl($file->toArray(), [
            'user_id' => $userId,
            'user_name' => $userName
        ]);
        
        View::assign([
            'previewUrl' => $previewUrl,
            'fileName' => $file->file_name
        ]);*/
        
        return View::fetch('wps/preview');
    }
}