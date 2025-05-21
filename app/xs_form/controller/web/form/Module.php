<?php

namespace app\xs_form\controller\web\form;

use app\admin\facade\service\CommonServiceFacade;
use xsframe\base\AdminBaseController;

class Module extends AdminBaseController
{
    protected $tableName = "xs_form_data_module";

    public function beforeSetPostData(&$updateData = [])
    {
        $map = $this->params['map'] ?? [];
        $address = $this->params['address'] ?? [];
        $birthday = $this->params['birthday'] ?? [];

        $updateData['longitude'] = $map['lng'];
        $updateData['latitude'] = $map['lat'];

        $updateData['province'] = $address['province'];
        $updateData['city'] = $address['city'];
        $updateData['area'] = $address['district'];

        $updateData['year'] = $birthday['year'];
        $updateData['month'] = $birthday['month'];
        $updateData['day'] = $birthday['day'];
    }

    public function afterPostResult(&$result = [])
    {
        $result['address'] = [
            'province' => $result['item']['province'],
            'city'     => $result['item']['city'],
            'district' => $result['item']['area'],
        ];

        $result['birthday'] = [
            'year'  => $result['item']['year'],
            'month' => $result['item']['month'],
            'day'   => $result['item']['day'],
        ];

        $areas = CommonServiceFacade::getAreas(true);
        $result['areas'] = $areas;
    }

    public function model()
    {
        if ($this->request->isPost()) {
            $this->success('响应成功');
        }
        return $this->template('model');
    }

    public function query()
    {
        $result = [
            'list' => []
        ];
        return $this->template('query', $result);
    }
}
