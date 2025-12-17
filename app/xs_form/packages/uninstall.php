<?php

// 目录地址: xs_form/packages/uninstall.php

$sql = "
   DROP TABLE IF EXISTS `ims_xs_form_data_basic`;
   DROP TABLE IF EXISTS `ims_xs_form_data_module`;
";

return $sql;