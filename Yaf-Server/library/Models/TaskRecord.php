<?php
/**
 * 打卡任务参与记录表 Model。
 * @author fingerQin
 * @date 2019-08-26
 */

namespace Models;

class TaskRecord extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_task_record';

    protected $primaryKey = 'id';

    /**
     * 更新时间字段。
     * 
     * @var string
     */
    protected $updateTime = false;
}