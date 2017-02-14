<?php
    
    /** @file xml_transform.php
     * @author Florian Wirthmüller
     * @version   1.20
     * @brief Script which generates a PASCAL VOC conform version (copy) of the xml files containing the annotations
     * @details if the function WriteXML() (specified in io.js) is called, this script will also be called
     */
    
    /**********************************
     //include necessary function definitions
     *********************************/
    Include ('xmlTransformFunctions.php');
    
    /**********************************
     fetch parameter which containes the xml string
     *********************************/
    /** contains the xml string which is specified if the Ajax request (in WriteXML()) is started */
    $XmlContent = $_POST['XmlContent'];
    
    /** contains a simpleXMLElemnet which is easier to acces */
    $annotation = new SimpleXMLElement($XmlContent);
    
    /**********************************
     collect parameters of the image
     *********************************/
    
    // get filename and filename without type
    /** contains the name of the image file to which the xml belongs*/
    $filename = getFilename($XmlContent);
    /** array which contains the filename and the type ending*/
    $array_file = explode(".",$filename);
    
    /** contains the name of the image file to which the xml belongs without the type ending*/
    $filenameNoType = $array_file[0];
    
    /** contains the type ending of the image file to which the xml belongs*/
    $fileType = $array_file[1];
    
    // get folder
    /** contains the folder of the image file to which the xml belongs*/
    $folder = getFolder($XmlContent);
    
    //get source
    /** contains the database of the image file to which the xml belongs*/
    $database = getDatabase ($XmlContent);
    /** contains the source of the last annotation/editor*/
    $annoSrc = getAnnoSrc ($XmlContent);
    /** contains the source of the image
     * @todo load the source of the image from the mysql database and write it in the xml
     */
    $imgSrc = getImgSrc ($XmlContent);
    
    //get imageSize
    /** contains an array with the sizes of the image*/
    $imgSize = getImgSize ($folder, $filename);
    /** contains the width of the image*/
    $width = $imgSize[0];
    /** contains the height of the image*/
    $height = $imgSize[1];
    
    /// @cond excludes try from the documentation
    try {
        $depth = $imgSize[channels];
    } catch (Exception $e) {
        $depth = (string)'notPossible';
    }
    /// @endcond
    
    /** contains if the  there is an segmentation file belonging to the image*/
    $segmented = 0;
    
    
    /**********************************
     construct output of the image parameters
     *********************************/
    
    /** contains the filehandler for the output file*/
    
    /*$filesize = filesize("../../AnnotationsVOC/".(string)$folder."/".(string)$filenameNoType.".xml");
    
    $test = fopen("../../Annotations/".(string)$folder."/test.txt", "w+");
    fwrite($test, (string)$filesize);
    fclose($test);*/
    
    
    
    $outputFile = fopen("../../AnnotationsVOC/".(string)$folder."/".(string)$filenameNoType.".xml", "w+") or die("Unable to open file!");
    
    
    fwrite($outputFile, "<annotation>");
    fwrite($outputFile, "<folder>".(string)$folder."</folder>");
    fwrite($outputFile, "<filename>".(string)$filename."</filename>");
    
    fwrite($outputFile, "<source>");
    fwrite($outputFile, "<database>".(string)$database."</database>");
    fwrite($outputFile, "<annotation>".(string)$annoSrc."</annotation>");
    fwrite($outputFile, "<image>".(string)$imgSrc."</image>");
    fwrite($outputFile, "</source>");
    
    
    fwrite($outputFile, "<size>");
    fwrite($outputFile, "<width>".(string)$width."</width>");
    fwrite($outputFile, "<height>".(string)$height."</height>");
    fwrite($outputFile, "<depth>".(string)$depth."</depth>");
    fwrite($outputFile, "</size>");
    
    
    
    /**********************************
     collect and write parameters of all annotated objects
     *********************************/
    
    foreach ($annotation->object as $obj) {
        $type=-1;
        /*contains the type of the object:
         not set yet: -1
         for a polygon: 0
         for a bounding box: 1
         for a mask: 2
         */
        
        /** contains the information if the actual object is a obsolet one*/
        $deleted = $obj->deleted;
        $User = $obj->polygon->username;
        $UserLabeled = ($User!='ClassifierPropagation');
        
        //only active bounding boxes occur in the new xml
        if (($deleted < 1) and $UserLabeled)
        {
            
            $name = $obj->name;
            
            //convert these params from yes/no to binary values
            if ($obj->occluded == 'yes')
            {
                $occluded = 1;
            }
            else
            {
                $occluded = 0;
            }
            
            if ($obj->truncated == 'yes')
            {
                $truncated = 1;
            }
            else
            {
                $truncated = 0;
            }
            
            if ($obj->difficult == 'yes')
            {
                $difficult = 1;
            }
            else
            {
                $difficult = 0;
            }
            
            $pose = $obj->pose;
            
            
            //check if the object is a bounding box
            if($obj->type=='bounding_box')
            {
                //set type
                $type = 1;
                
                //initialize edges
                $xmin = (int)100000;
                $xmax = (int)-100000;
                $ymin = (int)100000;
                $ymax = (int)-100000;
                
                //collect edges
                foreach ($obj->polygon->pt as $p)
                {
                    
                    if ((int)$p->x < $xmin)
                    {
                        $xmin = (int)$p->x;
                    }
                    elseif ((int)$p->x > $xmax)
                    {
                        $xmax = (int)$p->x;
                    }
                    
                    if ((int)$p->y < $ymin)
                    {
                        $ymin = (int)$p->y;
                    }
                    elseif ((int)$p->y > $ymax)
                    {
                        $ymax = (int)$p->y;
                    }
                    
                }
            }
            //check if the object is a polygon (and not a bounding box)
            elseif(isset($obj->polygon))
            {
                //set type
                $type = 0;
            }
            //if the object is neither a polygon nor a bounding box it is a segment
            else
            {
                //set type and segmented flag
                $type = 2;
                $segmented = 1;
                //edges can be collected directly in this case
                $xmin= $obj->segm->box->xmin;
                $xmax= $obj->segm->box->xmax;
                $ymin= $obj->segm->box->ymin;
                $ymax= $obj->segm->box->ymax;
                
                //collect the name of the mask file for this segment
                $mask= $obj->segm->mask;
            }
            
            //write object parameters to xml
            fwrite($outputFile, "<object>");
            fwrite($outputFile, "<name>".(string)$name."</name>");
            fwrite($outputFile, "<pose>".ucfirst ((string)$pose)."</pose>");
            fwrite($outputFile, "<occluded>".(string)$occluded."</occluded>");
            fwrite($outputFile, "<truncated>".(string)$truncated."</truncated>");
            fwrite($outputFile, "<difficult>".(string)$difficult."</difficult>");
            
            if ($type==0)
            {
                fwrite($outputFile, "<polygon>");
                //Todo
                fwrite($outputFile, "</polygon>");
            }
            
            if ($type==1 || $type==2)
            {
                fwrite($outputFile, "<bndbox>");
                fwrite($outputFile, "<xmax>".(string)$xmax."</xmax>");
                fwrite($outputFile, "<xmin>".(string)$xmin."</xmin>");
                fwrite($outputFile, "<ymax>".(string)$ymax."</ymax>");
                fwrite($outputFile, "<ymin>".(string)$ymin."</ymin>");
                fwrite($outputFile, "</bndbox>");
            }
            if ($type==2)
            {
                fwrite($outputFile, "<mask>".(string)$mask."</mask>");
            }
            
            fwrite($outputFile, "</object>");
        }
    }
    
    fwrite($outputFile, "<segmented>".(string)$segmented."</segmented>");
    
    fwrite($outputFile, "</annotation>");
    
    fclose($outputFile);
    ?>
