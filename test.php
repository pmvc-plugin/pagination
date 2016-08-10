<?php
namespace PMVC\PlugIn\pagination;
use PHPUnit_Framework_TestCase;

\PMVC\Load::plug();
\PMVC\addPlugInFolders(['../']);

class PaginationTest extends PHPUnit_Framework_TestCase
{
    private $_plug = 'pagination';

    function setup()
    {
        \PMVC\plug($this->_plug);
    }

    function teardown()
    {
        \PMVC\unplug($this->_plug);
    }

    function testPlugin()
    {
        ob_start();
        print_r(\PMVC\plug($this->_plug));
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertContains($this->_plug,$output);
    }

    function testSync()
    {
        $p = \PMVC\plug($this->_plug, [BEGIN=>1, TOTAL=>2]);
        $p->sync($p['page'], $p);
        $this->assertEquals($p[BEGIN], $p['page'][BEGIN]);
        $this->assertEquals($p[TOTAL], $p['page'][TOTAL]);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     * @expectedExceptionMessageRegExp /(Argument 1 passed to)/
     */
    function testProcessAssignWrongType()
    {
        $p = \PMVC\plug($this->_plug);
        try {
            $p->process(new \PMVC\HashMap());
        } catch (TypeError $e) {
            throw new PHPUnit_Framework_Error(
                $e->getMessage(),
                0,
                $e->getFile(),
                $e->getLine()
            );
        }
    }

    /**
     * @expectedException LogicException
     */
     function testAssignEmptyPrePageNumber() 
     {
        $p = \PMVC\plug($this->_plug, [
            PRE_PAGE_NUM=>0
        ]);
        $p->process();
     }

    /**
     * @dataProvider processByPageProvider
     */
     function testProcessByPage(
        $num,
        $total, 
        $current,
        $expectedTotalPage,
        $expectedBegin,
        $expectedEnd
     ) {
        $p = \PMVC\plug($this->_plug,[
            PRE_PAGE_NUM=>$num,
            TOTAL=>$total,
            CURRENT_PAGE=>$current
        ]);
        $page = $p->process();
        $this->assertEquals(
            $expectedTotalPage,
            $page[TOTAL_PAGE],
            'Verify totalPage fail. '.print_r([
                $page[TOTAL_PAGE],
                func_get_args()
            ],true)
        );
        $this->assertEquals(
            $expectedBegin,
            $page[BEGIN],
            'Verify begin fail. '.print_r([
                $page[BEGIN],
                func_get_args()
            ],true)
        );
        $this->assertEquals(
            $expectedEnd,
            $page[END],
            'Verify end fail. '.print_r([
                $page[END],
                func_get_args()
            ],true)
        );
     }

     function processByPageProvider()
     {
        return [
            [
                /* num, total, current*/
                1, 0, 0,
                /*Expected totalPage, begin, end*/
                1, 0, 0
            ],
            [
                1, 1, 1,
                1, 0, 0 
            ],
            [
                1, 2, 2,
                2, 1, 1 
            ],
            [
                1, 2, 3,
                2, 1, 1 
            ]
        ];
     }

    /**
     * @dataProvider navProvider
     */
    function testCalNav(
        $num,
        $total, 
        $current,
        $listNum,
        $expectedListB,
        $expectedListE 
    ) {
        $p = \PMVC\plug($this->_plug,[
            PRE_PAGE_NUM=>$num,
            TOTAL=>$total,
            CURRENT_PAGE=>$current
        ]);
        $page = $p->process();
        $list = $p->calPageList($page, $listNum);
        $this->assertEquals(
            $expectedListB,
            $list[BEGIN],
            'Verify begin fail. '.print_r([
                $list,
                func_get_args()
            ],true)
        );
        $this->assertEquals(
            $expectedListE,
            $list[END],
            'Verify end fail. '.print_r([
                $list,
                func_get_args()
            ],true)
        );
    }

    public function navProvider()
    {
        return [
            [
                /*perPageNum, total, current*/
                1, 0, 0,
                /*List: num, begin, end*/
                2, 1, 1 
            ],
            [
                1,1,1,
                2,1,1
            ],
            [
                1,2,1,
                2,1,2
            ],
            [
                1,2,3,
                2,1,2
            ],
        ];
    }
    
    public function testCalBegin()
    {
        $p = \PMVC\plug($this->_plug,[
            PRE_PAGE_NUM=>2,
            TOTAL=>6,
            CURRENT_PAGE=>1
        ]);
        $page = $p->process();
        $p->calNav($page);
        $forward = new Page($page[FORWARD]);
        $this->assertEquals(
            2,
            $forward[BEGIN]
        );
        $this->assertEquals(
            3,
            $forward[END]
        );
    }

    public function testGenPageList()
    {
        $p = \PMVC\plug($this->_plug,[
            PRE_PAGE_NUM=>2,
            TOTAL=>6,
            BEGIN=>0
        ]);
        $p->process();
        $pages = $p->genPageList(2);
        $this->assertEquals([1,2,3],array_keys($pages['list']));
        $this->assertEquals(CURRENT_PAGE,$pages['list'][1]);
        $this->assertEquals(1,$pages[CURRENT_PAGE][0][CURRENT_PAGE]);
        $this->assertEquals(2,$pages['list'][2][CURRENT_PAGE]);
        $this->assertEquals(3,$pages['list'][3][CURRENT_PAGE]);
    }

    public function testGenPageListHasFirstPage()
    {
        $p = \PMVC\plug($this->_plug,[
            PRE_PAGE_NUM=>2,
            TOTAL=>10,
            BEGIN=>10
        ]);
        $p->process();
        $pages = $p->genPageList(2);
        $this->assertTrue(!empty($pages[FIRST_PAGE]));
    }

    public function testGenPageListHasLastPage()
    {
        $p = \PMVC\plug($this->_plug,[
            PRE_PAGE_NUM=>2,
            TOTAL=>10,
            BEGIN=>0
        ]);
        $p->process();
        $pages = $p->genPageList(2);
        $this->assertTrue(!empty($pages[LAST_PAGE]));
    }
}
