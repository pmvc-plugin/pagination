<?php
namespace PMVC\PlugIn\pagination;

use ArrayAccess;

\PMVC\l(__DIR__.'/src/page.php');

${_INIT_CONFIG}[_CLASS] = __NAMESPACE__.'\pagination';

const BEGIN = '0';
const END = '1';
const PER_PAGE_NUM = 'perPageNum';
const TOTAL = 'total';
const TOTAL_PAGE = 'totalPage';
const CURRENT_PAGE = 'currentPage';
const BACKWARD = 'backward';
const FORWARD = 'forward';
const LAST_PAGE = 'lastPage';
const FIRST_PAGE = 'firstPage';
/**
 * type: [begin|page]
 */
const TYPE = 'type';

#url
const URL = 'url';
const QUERY_B = 'b';
const QUERY_PAGE = 'page';

/**
 * @parameters int PER_PAGE_NUM 
 * @parameters int TOTAL 
 * @parameters int CURRENT_PAGE 
 * @parameters int BEGIN 
 */
class pagination extends \PMVC\PlugIn
{
    public function init()
    {
        // page count
        $this['page'] = new Page();
        if (!isset($this[PER_PAGE_NUM])) {
            $this[PER_PAGE_NUM] = 10;
        }
        if (!isset($this[TOTAL])) {
            $this[TOTAL] = 0;
        }

        // url
        if (!isset($this[QUERY_B])) {
            $this[QUERY_B] = QUERY_B;
        }
        if (!isset($this[QUERY_PAGE])) {
            $this[QUERY_PAGE] = QUERY_PAGE;
        }
    }

    public function getPage($currentPage=null, $url=null)
    {
        return new Page($currentPage, $url);
    }

    public function process(Page $page = null, ArrayAccess $copyFrom = null)
    {
        if (is_null($page)) {
            $page = $this['page'];
        }
        if (is_null($copyFrom)) {
            $copyFrom = $this;
        }
        $this->sync($page, $copyFrom);
        if (empty($page[PER_PAGE_NUM])) {
            return !trigger_error('Per page number can\'t  set to empty.');
        }
        if (isset($page[BEGIN])) {
            $page[CURRENT_PAGE] = floor(
                $page[BEGIN] / $page[PER_PAGE_NUM]
            ) + 1;
            $page[TYPE] = 'begin';
        } elseif (isset($page[CURRENT_PAGE])) {
            $page[TYPE] = 'page';
        } else {
            $page[CURRENT_PAGE] = 1;
            $page[TYPE] = 'page';
        }
        return $this->calBegin($page);
    }

    public function calBegin(Page $page)
    {
        $page[TOTAL_PAGE] = ceil($page[TOTAL] / $page[PER_PAGE_NUM]);
        if (empty($page[TOTAL_PAGE])) {
            $page[TOTAL_PAGE] = 1;
        }
        if (empty($page[CURRENT_PAGE])) {
            $page[CURRENT_PAGE] = 1;
        }
        if ($page[CURRENT_PAGE] > $page[TOTAL_PAGE]) {
            $page[CURRENT_PAGE] = $page[TOTAL_PAGE];
        }
        if (empty($page[BEGIN])) {
            $page[BEGIN] = ($page[CURRENT_PAGE] - 1 ) *
                $page[PER_PAGE_NUM];
        }
        $page[END] = $page[BEGIN]+$page[PER_PAGE_NUM]-1;
        if ($page[BEGIN] >= $page[TOTAL]) {
            $page[BEGIN] = $page[TOTAL]-1;
        }
        if ($page[END] >= $page[TOTAL]) {
            $page[END] = $page[TOTAL]-1;
        }
        return $page;
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

    public function calPageList(Page $page, $num)
    {
        if ($num < 2) {
            return !trigger_error('Page list number need greater than 2, You set to ['.$num.'].');
        }
        $middle = floor($num / 2);
        $begin = $page[CURRENT_PAGE] - $middle;
        if ($num%2===0) {
            $begin++;
        }
        if ($begin<=0) {
            $begin = 1;
        }
        $end = $begin + ($num-1);
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

    public function genPages(Page $page=null, $url=null)
    {
        if (is_null($page)) {
            $page = $this['page'];
        }
        $num = $page[TOTAL_PAGE];
        $pages = [];
        for ($i=1; $i<=$num; $i++) {
            $pages[$i] = $this->toArray(new Page($i, $url));
        }
        return $pages;
    }

    public function genPageList($num, Page $page=null, $url=null)
    {
        if (is_null($page)) {
            $page = $this['page'];
        }
        $this->calNav($page);
        $pages = [];
        $return = [CURRENT_PAGE=>$this->toArray($page)];
        $current = (int)$page[CURRENT_PAGE];
        $pages[$current] = CURRENT_PAGE;
        if ($num) {
            $liCount = $this->calPageList($page, $num);
            for($i=$liCount[BEGIN]; $i<=$liCount[END]; $i++){
                if (strcmp($i,$current)!==0) {
                    $pages[$i] = $this->toArray(new Page($i, $url));
                }
            }
        }
        if (!empty($page[FIRST_PAGE])) {
            if (\PMVC\value($liCount,[BEGIN],-1) > $page[FIRST_PAGE]) {
                $firstPage = $this->toArray(new Page($page[FIRST_PAGE], $url));
                if (strcmp(2, $liCount[BEGIN]) === 0) {
                    $pages[1] = $firstPage;
                } else {
                    $return[FIRST_PAGE] = $firstPage;
                }
            }
        }
        if (isset($page[FORWARD])) {
            $return[CURRENT_PAGE][FORWARD] = $this->toArray(new Page($page[FORWARD], $url));
        }
        if (isset($page[BACKWARD])) {
            $return[CURRENT_PAGE][BACKWARD] = $this->toArray(new Page($page[BACKWARD], $url));
        }
        if (!isset($pages[$page[LAST_PAGE]]) && !empty($page[LAST_PAGE])) {
            $lastPage = $this->toArray(new Page(
                $page[LAST_PAGE],
                $url
            ));
            if (isset($i) && strcmp($i, $page[LAST_PAGE])===0) {
                $pages[$i] = $lastPage;
            } else {
                $return[LAST_PAGE] = $lastPage;
            }
        }
        ksort($pages);
        foreach($pages as $p){
            $return['list'][] = $p;
        }
        return $return;
    }

    public function toArray(Page $page)
    {
        $arrPage = \PMVC\get($page);
        foreach($arrPage as $k=>$v){
            if(is_null($v)){
                unset($arrPage[$k]);
            }
        }
        if (isset($arrPage[URL])) {
            $arrPage[URL] = (string)$arrPage[URL];
        }
        return $arrPage;
    }

    public function sync(Page $page, ArrayAccess $copyFrom)
    {
        $keys = [
            PER_PAGE_NUM,
            TOTAL,
            CURRENT_PAGE,
            BEGIN,
        ];
        foreach($keys as $k){
            if (isset($copyFrom[$k]) &&
                !isset($page[$k])
               ) {
                $page[$k] = $copyFrom[$k];
            }
        }
    }

}
