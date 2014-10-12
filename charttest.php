<?php
 /* pChart library inclusions */
 include("/pChart2.1.4/class/pData.class.php");
 include("/pChart2.1.4/class/pDraw.class.php");
 include("/pChart2.1.4/class/pImage.class.php");
 
 $myDataset = array(0, 1, 1, 2, 3, 5, 8, 13);
 $myData = new pData();
 $myData->addPoints($myDataset);
 $myData->setSerieTicks("Last year",8);
 $myData->addPoints(array("V0","V1","V2","V3","V4","V5","V6","V7"),"Labels");
 $myData->setAbscissa("Labels");
 
 $myImage = new pImage(800, 600, $myData);
 
 $myImage->Antialias = FALSE;
 
 /* Create a solid background */
 $Settings = array("R"=>179, "G"=>217, "B"=>91);
 $myImage->drawFilledRectangle(0,0,700,330,$Settings);
 
 $myImage->setFontProperties(array(
 		"FontName" => "c:/wamp/www/idb/pChart2.1.4/fonts/Silkscreen.ttf",
 		"FontSize" => 10));
 
 
 
 $myImage->setGraphArea(25,25, 475,275);
 
 $myImage->drawScale();
 
 $myImage->drawBarChart();
 
 header("Content-Type: image/png");
 $myImage->Render(null);





















?>