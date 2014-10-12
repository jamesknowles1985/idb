<?PHP
    include('includes/db_Mylib.php');
    include('includes/db_Ifxlib.php');
    require('includes/table_lib.php');
// includes/idb.ini contains connection information for both MySQL and Informix databases
    $myIniFile = parse_ini_file ("includes/idb.ini", TRUE);
//create the MySQL connection and open it
    $Myconfig = new Myconfig($myIniFile['IDBMYSQL']['server'], $myIniFile['IDBMYSQL']['login'], $myIniFile['IDBMYSQL']['password'], 
    $myIniFile['IDBMYSQL']['database'], $myIniFile['IDBMYSQL']['extension'],$myIniFile['IDBMYSQL']['mysqlformat']); 
  	$Mydb = new Mydb($Myconfig);
    $Mydb->openConnection();
//create the Informix connection and open it    
    $Ifxconfig = new Ifxconfig($myIniFile['IDBIFX']['odbc'], $myIniFile['IDBIFX']['login'], $myIniFile['IDBIFX']['password']);
  	$Ifxdb = new Ifxdb($Ifxconfig);
    $Ifxdb->openConnection();
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
//read key cell values from last run     
    $Mysql = $Mydb->query("SELECT idb_snapshot_id, idb_snapshot_v1, idb_snapshot_v2 from idb_snapshot order by idb_snapshot_date desc limit 1");
    foreach($Mysql as $keyline) {
    $sn_previous1 = $keyline ['idb_snapshot_v1'];
    $sn_previous2 = $keyline ['idb_snapshot_v2'];
    }
//SQL request for Informix
//generate current parts stock value       
    $Ifxsql1 = $Ifxdb->query1("select round((sum(pff_q * pff_cost)),2) from pff, pro where bra_id in (select bra_id from bra where cpy_id = '0002')
and pro.pro_pelmo = 1
and pff.pmf_id = pro.pmf_id and pff.pro_id = pro.pro_id ");
//generate current receivables
    $Ifxsql2 = $Ifxdb->query1("select sum(gtd_credit - gtd_debit) from gtd, gtr
    where gtd_mat_code <> 3 and gla_id in ('15000020', '15000021') and gtr.cpy_id = '0002'
    and gtd.gtr_id = gtr.gtr_id
    and gtr.csc_id is not null");

//generate table with the results of the MySQL query
//    $tbl = new HTML_Table('', 'demoTbl', 1, array('cellpadding'=>4, 'cellspacing'=>0) );
//    $tbl->addCaption('MySQL result', 'cap', array('id'=> 'tblCap') );

//    $tbl->addRow();
    // arguments: cell content, class, type (default is 'data' for td, pass 'header' for th)
    // can include associative array of optional additional attributes
//    $tbl->addCell('Cell', 'first', 'header');
//    $tbl->addCell('Value', '', 'header');
    
//    foreach($Mysql as $product) {
//        $tbl->addRow();
//            $tbl->addCell($product['idb_snapshot_id']);
//            $tbl->addCell($product['idb_snapshot_v1']);
//    }
//echo $tbl->display();

//generate table with results of Informix query    
    $tbl2 = new HTML_Table('', 'demoTbl2', 1, array('cellpadding'=>4, 'cellspacing'=>0) );
    $tbl2->addCaption('<b>Key Management Indicators</b>', 'cap', array('id'=> 'tblCap') );
    $tbl2->addRow();
    // arguments: cell content, class, type (default is 'data' for td, pass 'header' for th)
    // can include associative array of optional additional attributes
    $tbl2->addCell('Key', 'first', 'header');
    $tbl2->addCell('Previous Value', '', 'header');
    $tbl2->addCell('Current Value', '', 'header');
    $tbl2->addCell('Drill Down', '', 'header');
//parts stock
    while(odbc_fetch_row($Ifxsql1)){
      $tbl2->addRow();
      $tbl2->addCell ($sn_title1);
      $tbl2->addCell($sn_previous1);
      $tbl2->addCell (odbc_result($Ifxsql1, 1));
      $tbl2->addCell ('<a href="parts_stock1.php"><img src="./images/drill.ico" alt="Drill Down" height="42" width="42"></a>');
    }
//receivables    
    while(odbc_fetch_row($Ifxsql2)){
      $tbl2->addRow();
      $tbl2->addCell ($sn_title2);
      $tbl2->addCell ($sn_previous2);
      $tbl2->addCell (odbc_result($Ifxsql2, 1));
      $tbl2->addCell ('<a href="receivables1.php"><img src="./images/drill.ico" alt="Drill Down" height="42" width="42"></a>');
    }
    
    echo $tbl2->display();

    $Mysql = $Mydb->query("insert into idb_snapshot (idb_snapshot_id, idb_snapshot_v1, idb_snapshot_v2, idb_snapshot_date)
    values(0,".odbc_result($Ifxsql1, 1).", ".odbc_result($Ifxsql2, 1).", now())");
    $Mydb->closeConnection();
    $Ifxdb->closeConnection();
?>

