<?php
if (!defined('PROJECT_ROOT')) define('PROJECT_ROOT', '/var/php_scripts/project/');
if (!defined('FILE_DEFAULT_DIR')) define('FILE_DEFAULT_DIR', PROJECT_ROOT.'files'.DIRECTORY_SEPARATOR);
if (!defined('S_APP_DIR')) define('S_APP_DIR', PROJECT_ROOT.'app'.DIRECTORY_SEPARATOR);
if (!defined('S_CONF_DIR')) define('S_CONF_DIR', S_APP_DIR . 'conf'.DIRECTORY_SEPARATOR);
if (!defined('S_LIBS_DIR')) define('S_LIBS_DIR', S_APP_DIR . 'libs'.DIRECTORY_SEPARATOR);
if (!defined('S_SERVICES_DIR')) define('S_SERVICES_DIR', S_APP_DIR . 'services'.DIRECTORY_SEPARATOR);
if (!defined('S_FUNC')) define('S_FUNC', S_APP_DIR . 'functions'.DIRECTORY_SEPARATOR);
if (!defined('S_CLS')) define('S_CLS', S_APP_DIR . 'libs'.DIRECTORY_SEPARATOR);
if (!defined('S_DATA')) define('S_DATA', S_APP_DIR . 'data'.DIRECTORY_SEPARATOR);
if (!defined('GALLERY_LINK')) define('GALLERY_LINK', 'https://qors.io/files/');
if (!defined('BIDLAW_GALLERY_LINK')) define('BIDLAW_GALLERY_LINK', 'https://s.bidlaw.io/files/');
if (!defined('REALTOR_LOGO')) define('REALTOR_LOGO', 'https://bizart.io/files/realtors/');
if (!defined('OPERISM_LOGO')) define('OPERISM_LOGO', 'https://calidigi.b-cdn.net/template/files/img/logo-biz/files/');
if (!defined('BOOKING_LOGO')) define('BOOKING_LOGO', 'https://hotel.calidigi.com/dataset/images/');
if (!defined('VIDEO_LINK')) define('VIDEO_LINK', 'https://calidigi.b-cdn.net/videos/');
if (!defined('STRIPE_SECRET')) define('STRIPE_SECRET', 'sk_test_51JL4bxGZnpiTVszUm03wcR3C5OYuWJ1Bj3oeEtCfoyPLaUhxbTBzqgUbclzYJEMuZrhRqX20W7DyiiWuBLzELplO00HpV8ga6w');
?>