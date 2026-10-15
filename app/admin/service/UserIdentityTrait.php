<?php

namespace app\admin\service;

/**
 * 用户身份解析Trait
 * 从请求中已注入的用户信息解析用户ID和类型。
 * 共享层只消费认证结果，不依赖任何具体应用的业务表。
 *
 * 依赖前置条件：
 * - 请求经过 AuthLoginMiddleware 中间件处理
 * - 中间件会在 request 对象上注入 userInfo（WxUser模型）和 userType（1=学员 2=教师）
 *
 * @author fengzi
 */
trait UserIdentityTrait
{
    /** @var int 当前已认证用户ID */
    protected $currentUserId = 0;

    /** @var array 当前业务用户完整信息 */
    protected $currentUserInfo = [];

    /** @var int 当前用户类型 1=学员 2=教师 */
    protected $currentUserType = 0;

    /**
     * 解析用户身份
     * 直接使用中间件注入的认证信息，避免共享层查询应用业务表。
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

        $userInfo = is_array($wxInfo) ? $wxInfo : $wxInfo->toArray();

        // 用户类型仍由认证中间件判定。
        $this->currentUserType = $this->request->userType;
        $this->currentUserId = intval($userInfo['id'] ?? 0);
        $this->currentUserInfo = $userInfo;
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
