<?php
    /** @file xmlTransformFunctions.php
     * @author Florian Wirthmüller
     * @version   1.0
     * @brief contains all functions which are necessary to extract the parameters in the script xml_transform.php
     */
    
    /**
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
    
 
    /**
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
    
    /**
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
    
    /**
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
            {
                
                //if object is a polygon or a bounding box
                if ( isset ( $obj->polygon->username) )
                {
                    $lastUser = $obj->polygon->username;
                }
                //otherwise it is a segment
                else
                {
                    $lastUser = $obj->segm->username;
                }
            }
        }
        
        return $lastUser;
    }
    
    /**
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
    
    /**
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
?>
