<?php


namespace app\store\controller\pc;

use app\store\facade\service\AreaServiceFacade;
use app\store\facade\service\UserAddressServiceFacade;
use xsframe\exception\ApiException;

class UserAddress extends Base
{

    public function index()
    {
        $addressList = UserAddressServiceFacade::getAll(['mid' => $this->userId, 'deleted' => 0], "*", "isdefault desc");
        $result      = [
            'addressList' => $addressList
        ];
        return $this->success($result);
    }

    public function getList()
    {
        $addressInfo = UserAddressServiceFacade::getInfo(['mid' => $this->userId, 'id' => $this->params['id']]);
        $result      = [
            'addressInfo' => $addressInfo
        ];
        return $this->success($result);
    }

    public function detail()
    {
        $addressInfo = UserAddressServiceFacade::getInfo(['mid' => $this->userId, 'id' => $this->params['id']]);
        $result      = [
            'addressInfo' => $addressInfo
        ];
        return $this->success($result);
    }

    public function add()
    {
        $addressId = $this->params['id'];
        $realname  = $this->params['realname'];
        $mobile    = $this->params['mobile'];
        $province  = $this->params['province'];
        $city      = $this->params['city'];
        $area      = $this->params['area'];
        $address   = $this->params['address'];
        $isDefault = $this->params['isdefault'];

        if (empty($realname) || empty($mobile) || empty($province) || empty($city) || empty($area)) {
            throw new ApiException("参数错误");
        }

        $insertData = [
            'uniacid'   => $this->uniacid,
            'mid'       => $this->userId,
            'realname'  => $realname,
            'mobile'    => $mobile,
            'province'  => $province,
            'city'      => $city,
            'area'      => $area,
            'address'   => $address,
            'isdefault' => $isDefault,
        ];
        if (!empty($isDefault)) {
            UserAddressServiceFacade::updateInfo(['isdefault' => 0], ['mid' => $this->userId]);
        } else {
            $addressTotal = UserAddressServiceFacade::getTotal(['mid' => $this->userId, 'deleted' => 0]);
            if ($addressTotal <= 0) {
                $insertData['isdefault'] = 1;
            }
        }
        if (empty($addressId)) {
            $addressId = UserAddressServiceFacade::insertInfo($insertData);
        } else {
            UserAddressServiceFacade::updateInfo($insertData, ['id' => $addressId]);
        }

        $result = [
            'addressId' => $addressId
        ];
        return $this->success($result);
    }

    public function delete()
    {
        $id        = $this->params['id'] ?? 0;
        $isDeleted = UserAddressServiceFacade::updateInfo(['deleted' => 1], ['id' => $id, 'mid' => $this->userId]);
        $result    = ['isDeleted' => $isDeleted];
        return $this->success($result);
    }

    public function edit()
    {
        $id          = $this->params['id'] ?? 0;
        $addressInfo = UserAddressServiceFacade::getInfo(['id' => $id, 'mid' => $this->userId]);
        $result      = ['addressInfo' => $addressInfo];
        return $this->success($result);
    }

    public function area()
    {
        $type     = $this->params['type'];
        $parentId = $this->params['parentId'];

        $where = [
            'area_parent_id' => 0
        ];
        if ($type == 'city') {
            $where = [
                'area_parent_id' => $parentId
            ];
        } else {
            if ($type == 'area') {
                $where = [
                    'area_parent_id' => $parentId
                ];
            }
        }
        $list = AreaServiceFacade::getAll($where);

        $result = [
            'list' => $list
        ];
        return $this->success($result);
    }


}