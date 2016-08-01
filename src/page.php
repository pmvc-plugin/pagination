<?php
namespace PMVC\PlugIn\pagination;

use PMVC\HashMap;
use InvalidArgumentException;


class Page extends HashMap
{
    public function __construct($currentPage=null)
    {
        parent::__construct();
        if (!is_null($currentPage)) {
            $this[CURRENT_PAGE] =  $currentPage;
            $p = \PMVC\plug('pagination');
            $p->process($this);
        }
    }

    protected function getInitialState()
    {
        return [
            BEGIN=>null,
            END=>null,
            PRE_PAGE_NUM=>null,
            TOTAL=>null,
            TOTAL_PAGE=>null,
            CURRENT_PAGE=>null,
            TYPE=>null,

            //nav
            BACKWARD=>null,
            FORWARD=>null,
            FIRST_PAGE=>null,
            LAST_PAGE=>null
        ];
    }

    public function verifyInt($k, $v)
    {
        if (!is_numeric($v)) {
            throw new OutOfRangeException('Value is not int. ['.$k.'=>'.$v.']');
        }
        if ($v < 0) {
            $v = 0;
        }
        return parent::offsetSet($k, (int)$v);
    }

    public function verify_0($v)
    {
        return $this->verifyInt('0', $v);
    }

    public function verify_1($v)
    {
        return $this->verifyInt('1', $v);
    }

    public function verify_currentPage($v)
    {
        return $this->verifyInt('currentPage', $v);
    }

    public function offsetSet($k, $v)
    {
        if (is_callable([$this,'verify_'.$k])) {
            return call_user_func(
                [$this,'verify_'.$k],
                $v
            );
        }
        if (array_key_exists($k,$this)) {
            throw new InvalidArgumentException('Invalid key. ['.$k.']');
        }
        return parent::offsetSet($k, $v);
    }

    public function getLimit()
    {
        $limit = 'LIMIT %d,%d';
        return sprintf(
            $limit,
            $this[BEGIN],
            $this[PRE_PAGE_NUM]
        );
    }
}
