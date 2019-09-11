<?php
/**
 * 物流业务封装。
 * @author fingerQin
 * @date 2019-09-11
 */

namespace Services\Mall;

use Models\Express;

class Logistics extends \Services\AbstractBase
{
    /**
     * 获取快递厂商列表(键值对形式)。
     *
     * @return array
     */
    public static function getExpressKeyValue()
    {
        $expressList = (new Express())->fetchAll(['name', 'code'], ['status' => Express::STATUS_YES]);
        $result = [];
        foreach ($expressList as $express) {
            $result[$express['code']] = $express['name'];
        }
        return $result;
    }
}