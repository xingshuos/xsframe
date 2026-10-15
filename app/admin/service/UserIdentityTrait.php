<?php

namespace app\admin\service;

use think\facade\Db;

/**
 * 用户身份解析Trait
 * 从请求中的微信用户信息解析出业务用户ID和类型
 * 设计为共享层组件，可被 sz_contest 和 sz_jiaowu 模块的控制器共同引用
 *
 * 依赖前置条件：
 * - 请求经过 AuthLoginMiddleware 中间件处理
 * - 中间件会在 request 对象上注入 userInfo（WxUser模型）和 userType（1=学员 2=教师）
 *
 * @author fengzi
 */
trait UserIdentityTrait
{
    /** @var int 当前业务用户ID（学员表或教师表的主键） */
    protected $currentUserId = 0;

    /** @var array 当前业务用户完整信息 */
    protected $currentUserInfo = [];

    /** @var int 当前用户类型 1=学员 2=教师 */
    protected $currentUserType = 0;

    /**
     * 解析用户身份
     * 从中间件注入的微信用户信息中，通过手机号匹配业务用户
     * 学员通过 contact_phone 匹配 sz_jiaowu_student 表
     * 教师通过 mobile 匹配 sz_jiaowu_teacher 表
     * @return void
     * @author fengzi
     */
    protected function resolveUserIdentity()
    {
        // 获取中间件注入的微信用户信息
        $wxInfo = $this->request->userInfo;
        if (empty($wxInfo)) {
            return;
        }

        // 从微信用户信息中提取手机号和用户类型
        $mobile = $wxInfo['mobile'] ?? '';
        $this->currentUserType = $this->request->userType;

        // 根据用户类型查询对应的业务表
        if (1 == $this->currentUserType) {
            // 学员：通过联系电话匹配
            $userInfo = Db::name('sz_jiaowu_student')->where('contact_phone', $mobile)->find();
        } else {
            // 教师：通过手机号匹配
            $userInfo = Db::name('sz_jiaowu_teacher')->where('mobile', $mobile)->find();
        }

        // 设置解析结果
        $this->currentUserId = $userInfo['id'] ?? 0;
        $this->currentUserInfo = $userInfo ?? [];
    }

    /**
     * 判断当前用户是否为学员
     * @return bool
     * @author fengzi
     */
    protected function isStudent()
    {
        return $this->currentUserType == 1;
    }

    /**
     * 判断当前用户是否为教师
     * @return bool
     * @author fengzi
     */
    protected function isTeacher()
    {
        return $this->currentUserType == 2;
    }
}
