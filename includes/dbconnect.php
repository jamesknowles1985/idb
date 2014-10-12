<?php 
//SQL request for MySQL
//get titles for free key cells
    $Mysql = $Mydb->query("select idb_setting_id, idb_setting_char from idb_settings where idb_setting_type = 1 order by idb_setting_id");
    foreach($Mysql as $titleline) {
      switch ($titleline ['idb_setting_id']) {
        case 1:
        $sn_title1 = $titleline ['idb_setting_char'];
        break;
        case 2:
        $sn_title2 = $titleline ['idb_setting_char'];
        break;
      }
    }
?>