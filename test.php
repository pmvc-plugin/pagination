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
        $p->sync($p['page']);
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

     function testProcessByPage()
     {

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
        $list = $p->genPageList($page, $listNum);
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
}
