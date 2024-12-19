<?php

namespace app\xs_form\controller\web;

use xsframe\base\AdminBaseController;
use xsframe\facade\service\DbServiceFacade;
use xsframe\util\ArrayUtil;

class Sets extends AdminBaseController
{
    public function account()
    {
        $accountSettings = $this->settingsController->getAccountSettings($this->uniacid, 'settings');

        if ($this->request->isPost()) {
            $settingsData = $this->params['data'] ?? [];

            $settingsData = ArrayUtil::customMergeArrays($accountSettings, $settingsData);
            $data['settings'] = serialize($settingsData);

            DbServiceFacade::name("sys_account")->updateInfo($data, ['uniacid' => $this->uniacid]);

            $this->settingsController->reloadAccountSettings($this->uniacid);
            $this->success();
        }

        $result = [
            'accountSettings' => $accountSettings,
        ];
        return $this->template('account', $result);
    }
}
