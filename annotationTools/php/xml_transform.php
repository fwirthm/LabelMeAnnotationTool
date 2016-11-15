<?php
    
    /*!
     *  \brief     Script which generates a PASCAL VOC conform version (copy) of the xml files containing the annotations.
     *  \details   if WriteXML() (specified in io.js) is called this script will also be called.
     *  \author    Florian Wirthmüller
     *  \version   1.1
     *  \date      14.11.2016
     *  \bug       no handling for concurrent data access.
     */
    
    
    /*!
     * returns the filename specified in the given xml string.
     * @param[out] $filename filename which is specified in the given xml string.
     * @param[in]  $xml  XML string.
     */
    function getFilename ($xml)
    {
        
        $annotation = new SimpleXMLElement($xml);
        $filename = $annotation->filename;
        $filename = preg_replace( "/\r|\n/", "", $filename );
        
        return $filename;
    }
    
 
    /*!
     * returns the folder specified in the given xml string.
     * @param[out] $folder folder which is specified in the given xml string.
     * @param[in]  $xml  XML string.
     */
    function getFolder ($xml)
    {
        
        $annotation = new SimpleXMLElement($xml);
        $folder = $annotation->folder;
        $folder = preg_replace( "/\r|\n/", "", $folder );
        
        return $folder;
    }
    
    /*!
     * returns the source database specified in the given xml string.
     * @param[out] $database source database which is specified in the given xml string.
     * @param[in]  $xml  XML string.
     */
    function getDatabase ($xml)
    {
        
        $annotation = new SimpleXMLElement($xml);
        $database = $annotation->source->database;
        $database = preg_replace( "/\r|\n/", "", $database );

        return $database;
    }
    
    
    
    /*!
     * returns the source of the last annotion/edit specified in the given xml string.
     * @param[out] $lastUser username of the last editor which is specified in the given xml string.
     * @param[in]  $xml  XML string.
     */
    function getAnnoSrc ($xml)
    {
        
        $annotation = new SimpleXMLElement($xml);
        
        
        $lastTimestamp = 0;
        $lastUser = 'None';
        foreach ($annotation->object as $obj) {
            $date = $obj->date;
            $timestamp = strtotime($date);
            if ($timestamp > $lastTimestamp)
                $lastUser = $obj->polygon->username;
        }
        
        return $lastUser;
    }
    
    /*!
     * returns the source of the image specified in the given xml string.
     * @param[out] $imgSrc source of the image which is specified in the given xml string.
     * @param[in]  $xml  XML string.
     */
    function getImgSrc ($xml)
    {
        
        $annotation = new SimpleXMLElement($xml);
        $imgSrc = (string)'Todo'; //todo get from MySQL Database?
        
        return $imgSrc;
    }
    
    /*!
     * returns the size of the image which is specified by folder and flename.
     * @param[out] $imgSize size of the image which is specified in the given parameters.
     * @param[in]  $folder  folder which contains the image.
     * @param[in]  $filename  filename of the image.
     */
    function getImgSize ($folder, $filename)
    {
        $imgSize = getimagesize("../../Images/".(string)$folder."/".(string)$filename);
        
        return $imgSize;
    }
    
    
    // fetch parameter which containes the xml string
    $XmlContent = $_POST['XmlContent'];
    
    
    $annotation = new SimpleXMLElement($XmlContent);
    
    
    //collect parameters
    
    // get filename and filename without type
    $filename = getFilename($XmlContent);
    $array_file = explode(".",$filename);
    $filenameNoType = $array_file[0];
    $fileType = $array_file[1];
    
    // get folder
    $folder = getFolder($XmlContent);
    
    //get source
    $database = getDatabase ($XmlContent);
    $annoSrc = getAnnoSrc ($XmlContent);
    $imgSrc = getImgSrc ($XmlContent);
    
    //get imageSize
    $imgSize = getImgSize ($folder, $filename);
    $width = $imgSize[0];
    $height = $imgSize[1];
    try {
        $depth = $imgSize[channels];
    } catch (Exception $e) {
        $depth = (string)'notPossible';
    }
    
    
    //make output
    $myfile = fopen("../../AnnotationsVOC/".(string)$folder."/".(string)$filenameNoType.".xml", "w") or die("Unable to open file!");
    
    fwrite($myfile, "<annotation>");
    fwrite($myfile, "<folder>".(string)$folder."</folder>");
    fwrite($myfile, "<filename>".(string)$filename."</filename>");
    
    fwrite($myfile, "<source>");
    fwrite($myfile, "<database>".(string)$database."</database>");
    fwrite($myfile, "<annotation>".(string)$annoSrc."</annotation>");
    fwrite($myfile, "<image>".(string)$imgSrc."</image>");
    fwrite($myfile, "</source>");
    
    
    fwrite($myfile, "<size>");
    fwrite($myfile, "<width>".(string)$width."</width>");
    fwrite($myfile, "<height>".(string)$height."</height>");
    fwrite($myfile, "<depth>".(string)$depth."</depth>");
    fwrite($myfile, "</size>");
    
    fwrite($myfile, "<segmented>".(string)'0'."</segmented>");
    
    foreach ($annotation->object as $obj) {
        $deleted = $obj->deleted;
        
        //only active bounding boxes occur in new xml
        if ($deleted < 1)
        {
            
            $name = $obj->name;
            $truncated = $obj->occluded;
            $difficult = $obj->difficult;
            
            $xmin = (int)100000;
            $xmax = (int)-100000;
            $ymin = (int)100000;
            $ymax = (int)-100000;
            
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
            fwrite($myfile, "<object>");

        
            fwrite($myfile, "<name>".(string)$name."</name>");
            fwrite($myfile, "<pose>".(string)'Unspecified'."</pose>");
            fwrite($myfile, "<difficult>".(string)'0'."</difficult>");
            fwrite($myfile, "<truncated>".(string)$truncated."</truncated>");
            fwrite($myfile, "<difficult>".(string)$difficult."</difficult>");
            
            fwrite($myfile, "<bndbox>");
            fwrite($myfile, "<xmax>".(string)$xmax."</xmax>");
            fwrite($myfile, "<xmin>".(string)$xmin."</xmin>");
            fwrite($myfile, "<ymax>".(string)$ymax."</ymax>");
            fwrite($myfile, "<ymin>".(string)$ymin."</ymin>");
            fwrite($myfile, "</bndbox>");
            
            fwrite($myfile, "</object>");
        }
    }
    
    
    fwrite($myfile, "</annotation>");

    fclose($myfile);
    ?>
