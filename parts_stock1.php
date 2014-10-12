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
    $Mysql = $Mydb->query("select idb_setting_id, idb_setting_key, idb_setting_char from idb_settings where idb_setting_type = 2 
    order by idb_setting_key");
    $manufacturer_data = array();
    $manufacturer_key = array();
    $manufacturer_name = array();
    $loopcount = 0;
//    $manufacturer_name = array();
    while ($row = mysqli_fetch_array($Mysql)) {
      $manufacturer_key[] = $row['idb_setting_key'];
      $manufacturer_name[] = $row['idb_setting_char'];
      $loopcount++;
    }
    $keystring = implode("','",$manufacturer_key);
//    echo $keystring.'<br>';
//    for($i=0; $i<$loopcount; $i++){
//    echo $manufacturer_name[$i]."<br>";
//    }

//SQL request for Informix
//generate current parts stock value     
    $Ifxsql1 = $Ifxdb->query1("select pff.pmf_id, round((sum(pff_q * pff_cost)),2) from pff, pro where bra_id in (select bra_id from bra where cpy_id = '0002')
    and pro.pro_pelmo = 1 and pff.pmf_id in ('".$keystring."') and pff.pmf_id = pro.pmf_id and pff.pro_id = pro.pro_id group by 1 order by 1 ");
    
    $Ifxsql2 = $Ifxdb->query1("select round((sum(pff_q * pff_cost)),2) from pff, pro where bra_id in (select bra_id from bra where cpy_id = '0002')
    and pro.pro_pelmo = 1 and pff.pmf_id not in ('".$keystring."') and pff.pmf_id = pro.pmf_id and pff.pro_id = pro.pro_id");

//generate table with results of Informix query    
    $tbl3 = new HTML_Table('', 'Parts Stock by manufacturer', 1, array('cellpadding'=>4, 'cellspacing'=>0) );
    $tbl3->addCaption('<b>Parts Stock by Manufacturer</b>', 'cap', array('id'=> 'tblCap') );

    $tbl3->addRow();
    // arguments: cell content, class, type (default is 'data' for td, pass 'header' for th)
    // can include associative array of optional additional attributes
    $tbl3->addCell('Mfr. Name', 'first', 'header');
    $tbl3->addCell('Mfr. Key', '', 'header');
    $tbl3->addCell('Stock Value', '', 'header');
    $tbl3->addCell('Drill Down', '', 'header');
//parts stock by manufacturer
    $rowindex = 0;
    $parts_stock1_total = 0;
    while(odbc_fetch_row($Ifxsql1)){
      $tbl3->addRow();
      $tbl3->addCell ($manufacturer_name[$rowindex]);
      $tbl3->addCell (odbc_result($Ifxsql1, 1));
      $tbl3->addCell (odbc_result($Ifxsql1, 2));
      $tbl3->addCell ('<a href="parts_stock1.php"><img src="./images/drill.ico" alt="Smiley face" height="42" width="42"></a>');
      $rowindex++;
      $parts_stock1_total = $parts_stock1_total + odbc_result($Ifxsql1, 2);
    }

    while(odbc_fetch_row($Ifxsql2)){    
      $tbl3->addRow();
      $tbl3->addCell('Others');
      $tbl3->addCell('Others');
      $tbl3->addCell(odbc_result($Ifxsql2, 1));
      $tbl3->addCell('');
      $parts_stock1_total = $parts_stock1_total + odbc_result($Ifxsql2, 1);
    }
    
    $tbl3->addRow();
    $tbl3->addCell ('');
    $tbl3->addCell ('Total');
    $tbl3->addCell ($parts_stock1_total);
    $tbl3->addCell ('');
    
    echo $tbl3->display();

    $Mydb->closeConnection();
    $Ifxdb->closeConnection();
?>
