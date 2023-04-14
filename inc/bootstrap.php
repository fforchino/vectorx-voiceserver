<?php

define("PROJECT_ROOT_PATH", "/web/htdocs/www.wondergarden.app/home/voiceserver/");
define("PROJECT_CACHE_PATH", "/web/htdocs/www.wondergarden.app/home/voiceserver/voicecache/");

// include main configuration file
require_once PROJECT_ROOT_PATH . "/inc/config.php";

// include the base controller file
require_once PROJECT_ROOT_PATH . "/Controller/Api/BaseController.php";

// include the use model file
require_once PROJECT_ROOT_PATH . "/Model/TTSModelBase.php";
require_once PROJECT_ROOT_PATH . "/Model/ResembleAiTTSModel.php";
require_once PROJECT_ROOT_PATH . "/Model/FakeYouTTSModel.php";

// A B E C E D A R I O
?>