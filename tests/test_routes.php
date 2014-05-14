<?php
require_once '../lib/Keen.php';

// route declarations, or includes are below
Keen::route('/', 'TestRoot', '', 'root');
Keen::route('/test/', 'TestTest', '', 'test');
Keen::route('/test/@test/', 'TestParam', '../tests/controllers/TestParam.php');

