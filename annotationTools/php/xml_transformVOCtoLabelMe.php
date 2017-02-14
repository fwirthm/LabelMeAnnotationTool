<?php
    
    /** @file xml_transformVOCtoLabelMe.php
     * @author Florian Wirthmüller
     * @version   1.0
     * @brief Script which generates a LabelMe conform version (copy) of the PASCAL VOC conform xml file
     */
    
    /**********************************
     fetch parameter which containes the xml string
     *********************************/
    /** contains the xml string which is specified if the Ajax request (in WriteXML()) is started */
    $VOCXml = $_POST['VOCXml'];
    
    $annotation = simplexml_load_file("../../".$VOCXml);
    $VOCXml = (string)$VOCXml;
    
    $LabelMeXml = str_replace("AnnotationsVOC", "Annotations", $VOCXml);
    /*$outputFile = fopen("../../Annotations/example_folder/test.txt", "x"); //or die("Unable to open file!");
    fwrite($outputFile, file_exists("../../".$VOCXml));
    fwrite($outputFile, "../../".$VOCXml);
    fclose($outputFile);*/
    
    if (file_exists("../../".$VOCXml)){
    
        $LabelMeXml = str_replace("AnnotationsVOC", "Annotations", $VOCXml);
    
        $outputFile = fopen("../../".$LabelMeXml, "x"); //or die("Unable to open file!");
    
        //load voc xml
    
        $filename = $annotation->filename;
    
        $image = "../../Images/example_folder/".$filename;
        list($width_, $height_, $type_, $attr_) = getimagesize ($image);
    
        $database = $annotation->source->database;
        $width = $annotation->size->width;
        $height = $annotation->size->height;
        $depth = $annotation->size->depth;
    
        $scale = $width_ / $width;
    
        $obj_name = $annotation->object->name;
        $obj_pose = $annotation->object->pose;
        $obj_truncated = $annotation->object->truncated;
        $obj_difficult = $annotation->object->difficult;
    
        $xmin = round(($annotation->object->bndbox->xmin)*$scale);
        $xmax = round(($annotation->object->bndbox->xmax)*$scale);
        $ymin = round(($annotation->object->bndbox->ymin)*$scale);
        $ymax = round(($annotation->object->bndbox->ymax)*$scale);
    
        fwrite($outputFile, "<annotation><folder>example_folder</folder><filename>");
        fwrite($outputFile, (string)$filename);
        fwrite($outputFile,"</filename><source><database>");
        fwrite($outputFile, (string)$database);
        fwrite($outputFile,"</database></source><imagesize><nrows>");
        fwrite($outputFile, (string)$height);
        fwrite($outputFile,"</nrows><ncols>");
        fwrite($outputFile, (string)$width);
        fwrite($outputFile,"</ncols></imagesize>");
        fwrite($outputFile,"<object><name>");
        fwrite($outputFile, (string)$obj_name);
        fwrite($outputFile,"</name><deleted>0</deleted><verified>0</verified><pose>");
        fwrite($outputFile, (string)$obj_pose);
        fwrite($outputFile,"</pose><truncated>");
        fwrite($outputFile, (string)$obj_truncated);
        fwrite($outputFile,"</truncated><difficult>");
        fwrite($outputFile, (string)$obj_difficult);
        fwrite($outputFile,"</difficult>");
        fwrite($outputFile, "<attributes></attributes><parts><hasparts></hasparts><ispartof></ispartof></parts><date>");
        fwrite($outputFile, date('d-M-Y H:i:s'));
        fwrite($outputFile,"</date><id>0</id><type>bounding_box</type><polygon><username>unknown</username><pt><x>");
        fwrite($outputFile,(string)$xmin);
        fwrite($outputFile,"</x><y>");
        fwrite($outputFile,(string)$ymin);
        fwrite($outputFile,"</y></pt><pt><x>");
        fwrite($outputFile,(string)$xmax);
        fwrite($outputFile,"</x><y>");
        fwrite($outputFile,(string)$ymin);
        fwrite($outputFile,"</y></pt><pt><x>");
        fwrite($outputFile,(string)$xmax);
        fwrite($outputFile,"</x><y>");
        fwrite($outputFile,(string)$ymax);
        fwrite($outputFile,"</y></pt><pt><x>");
        fwrite($outputFile,(string)$xmin);
        fwrite($outputFile,"</x><y>");
        fwrite($outputFile,(string)$ymax);
        fwrite($outputFile,"</y></pt>");
        fwrite($outputFile, "</polygon></object></annotation>");
        fclose($outputFile);
    }
    else{
        header('HTTP/1.1 500 Internal Server Booboo');
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('message' => 'ERROR', 'code' => 1337)));
    }
    
    ?>

