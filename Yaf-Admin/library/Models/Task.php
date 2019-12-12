<?php
/**
 * 打卡任务表 Model。
 * @author fingerQin
 * @date 2019-08-26
 */

namespace Models;

class Task extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_task';

    protected $primaryKey = 'taskid';
}