<?php
/**
 * 用户字典表。
 * @author fingerQin
 * @date 2019-08-08
 */

namespace Models;

class GmNameDict extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'gm_name_dict';

    protected $primaryKey = 'id';

    /**
     * 性别常量。
     */
    const SEX_FEMALE = 'female';
    const SEX_MALE   = 'male';


    /**
     * 名字类型。
     */
    const TYPE_SINGLE = 1; // 单字。
    const TYPE_DOUBLE = 2; // 双字。
}