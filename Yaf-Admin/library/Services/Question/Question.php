<?php
/**
 * 试题相关业务封装。
 * @author fingerQin
 * @date 2020-04-17
 */

namespace Services\Question;

class Question extends \Services\AbstractBase
{
    /**
     * 试卷列表。
     *
     * @param  int  $page   当前页码。
     * @param  int  $count  每页显示条数。
     *
     * @return array
     */
    public static function papers($page = 1, $count = 20)
    {

    }

    /**
     * 随机一道试题。
     *
     * @return array
     */
    public static function rand()
    {

    }

    /**
     * 获取用户答题记录。
     *
     * @param  int     $userid   用户 ID。
     * @param  string  $catCode  分类编码。
     * @param  int     $page     当前页码。
     * @param  int     $count    每页显示条数。
     *
     * @return array
     */
    public static function records($userid, $catCode = '', $page = 1, $count = 20)
    {

    }
}