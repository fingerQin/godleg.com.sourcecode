<?php
/**
 * 打卡任务主办方表 Model。
 * @author fingerQin
 * @date 2019-08-26
 */

namespace Models;

class TaskSponsor extends AbstractBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $tableName  = 'finger_task_sponsor';

    protected $primaryKey = 'sponsorid';
}