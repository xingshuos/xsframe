<?php


namespace app\store\controller\pc;

use app\store\facade\service\MemberServiceFacade;
use app\store\facade\service\SmsServiceFacade;
use think\facade\Route;

class Login extends Base
{
    public function index()
    {
        $username = $this->params['username'] ?? '';
        $password = $this->params['password'] ?? '';
        $backUrl  = $this->params['backUrl'] ?? '';

        if ($this->request->isPost()) {

            if (!empty($backUrl)) {
                $backUrl = (Route::buildUrl(base64_decode(urldecode($backUrl))))->suffix(false)->domain(true);
            } else {
                $backUrl = url('user/info', [], true, true);
            }

            MemberServiceFacade::login($username, $password);
            return $this->success(['backUrl' => strval($backUrl)]);
        }

        $result = [
            'backUrl' => $backUrl
        ];
        return $this->template('pc/login/login', $result);
    }

    public function register()
    {
        if ($this->request->isPost()) {
            $username = $this->params['username'] ?? '';
            $mobile   = $this->params['mobile'] ?? '';
            $password = $this->params['password'] ?? '';
            $code     = $this->params['code'] ?? '';
            SmsServiceFacade::checkSmsCode($mobile, $code);
            $isRegister = MemberServiceFacade::register($username, $mobile, $password);
            return $this->success(['isRegister' => $isRegister]);
        }
        return $this->template('pc/login/register');
    }

    public function forget()
    {
        if ($this->request->isPost()) {
            $mobile   = $this->params['mobile'] ?? '';
            $password = $this->params['password'] ?? '';
            $code     = $this->params['code'] ?? '';
            SmsServiceFacade::checkSmsCode($mobile, $code);
            $isUpdate = MemberServiceFacade::forget($mobile, $password);
            return $this->success(['isUpdate' => $isUpdate]);
        }
        return $this->template('pc/login/forget');
    }

    public function logout()
    {
        if ($this->request->isPost()) {
            $isLogout = MemberServiceFacade::logout();
            return $this->success(['isLogout' => $isLogout]);
        }
        return $this->template('pc/login/forget');
    }

}