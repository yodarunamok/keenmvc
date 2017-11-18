<?php
require_once '../lib/Keen.php';

// route declarations, or includes are below
KeenMVC\App::route('/', 'TestRoot', '', 'root');
KeenMVC\App::route('/test/', 'TestTest', '', 'test');
KeenMVC\App::route('/test/@test/', 'TestParam', '../tests/controllers/TestParam.php');

