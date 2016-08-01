<?php
namespace PMVC\PlugIn\pagination;

use LogicException;

\PMVC\l(__DIR__.'/src/page.php');

${_INIT_CONFIG}[_CLASS] = __NAMESPACE__.'\pagination';

const BEGIN = '0';
const END = '1';
const LIMIT = 'limit';
const PRE_PAGE_NUM = 'perPageNum';
const TOTAL = 'total';
const TOTAL_PAGE = 'totalPage';
const CURRENT_PAGE = 'currentPage';
const BACKWARD = 'backward';
const FORWARD = 'forward';
const LAST_PAGE = 'lastPage';
const FIRST_PAGE = 'firstPage';
const TYPE = 'type';

/**
 * @parameters int PRE_PAGE_NUM 
 * @parameters int TOTAL 
 * @parameters int CURRENT_PAGE 
 * @parameters int BEGIN 
 */
class pagination extends \PMVC\PlugIn
{
    public function init()
    {
        $this['page'] = new Page();
        if (!isset($this[PRE_PAGE_NUM])) {
            $this[PRE_PAGE_NUM] = 10;
        }

        if (!isset($this[TOTAL])) {
            $this[TOTAL] = 0;
        }
    }

    public function process(Page $page = null)
    {
        if (is_null($page)) {
            $page = $this['page'];
        }
        $this->sync($page);
        if (empty($page[PRE_PAGE_NUM])) {
            throw new LogicException('Pre page number can\'t  set to empty.');
        }
        if (isset($this[BEGIN])) {
            $page[CURRENT_PAGE] = floor(
                $page[BEGIN] / $page[PRE_PAGE_NUM]
            ) + 1;
            $page[TYPE] = 'begin';
        } elseif (isset($this[CURRENT_PAGE])) {
            $page[TYPE] = 'page';
        } else {
            throw new LogicException('Need set current page or begin.');
        }
        $this->calBegin($page);
        $limit = 'LIMIT %d,%d';
        $page[LIMIT] = sprintf(
            $limit,
            $page[BEGIN],
            $page[PRE_PAGE_NUM]
        );
        return $page;
    }

    public function calBegin(Page $page)
    {
        $page[TOTAL_PAGE] = $page[TOTAL] / $page[PRE_PAGE_NUM];
        if (empty($page[TOTAL_PAGE])) {
            $page[TOTAL_PAGE] = 1;
        }
        if (empty($page[CURRENT_PAGE])) {
            $page[CURRENT_PAGE] = 1;
        }
        if ($page[CURRENT_PAGE] > $page[TOTAL_PAGE]) {
            $page[CURRENT_PAGE] = $page[TOTAL_PAGE];
        }
        if (is_null($page[BEGIN])) {
            $page[BEGIN] = ($page[CURRENT_PAGE] - 1 ) *
                $page[PRE_PAGE_NUM];
        }
        $page[END] = $page[BEGIN]+$page[PRE_PAGE_NUM];
        if ($page[BEGIN] >= $page[TOTAL]) {
            $page[BEGIN] = $page[TOTAL]-1;
        }
        if ($page[END] >= $page[TOTAL]) {
            $page[END] = $page[TOTAL]-1;
        }
    }

    /**
     * set BACKWARD, FORWARD, FIRST_PAGE, LAST_PAGE
     */
    public function calNav(Page $page)
    {
        if (1!==$page[CURRENT_PAGE]) {
            $page[BACKWARD] = $page[CURRENT_PAGE] - 1;
            $page[FIRST_PAGE] = 1;
        }
        if ($page[TOTAL_PAGE] > $page[CURRENT_PAGE]) {
            $page[FORWARD] = $page[CURRENT_PAGE] + 1;
            $page[LAST_PAGE] = $page[TOTAL_PAGE];
        }
    }

    public function genPageList(Page $page, $num)
    {
        if ($num < 2) {
            throw new LogicException('Page list number need greater than 2, You set to ['.$num.'].');
        }
        $middle = floor($num / 2);
        $begin = $page[CURRENT_PAGE] - $middle;
        if ($num%2===0) {
            $begin--;
        }
        if ($begin<=0) {
            $begin = 1;
        }
        $end = $page[CURRENT_PAGE] + $middle;
        if ($end > $page[TOTAL_PAGE]) {
            $end = $page[TOTAL_PAGE];
        }
        if (($end - $begin) < ($num-1)) {
            $begin = $end - $num+1;
            if ($begin<=0) {
                $begin = 1;
            }
        }
        return [
            BEGIN=>$begin,
            END=>$end
        ];
    }

    public function sync(Page $page)
    {
        $keys = [
            PRE_PAGE_NUM,
            TOTAL,
            CURRENT_PAGE,
            BEGIN
        ];
        foreach($keys as $k){
            if (isset($this[$k])) {
                $page[$k] = $this[$k];
            }
        }
    }

}
