<?php
require('vendor/autoload.php');

use \PMVC\PlugIn\pagination as pg;

\PMVC\Load::plug(['pagination'=>null], ['../']);
$p = \PMVC\plug('pagination', [
    pg\TOTAL=>33,
    pg\BEGIN=>4,
    pg\PRE_PAGE_NUM=>5
]);
$page = $p->process();
var_dump($page);
