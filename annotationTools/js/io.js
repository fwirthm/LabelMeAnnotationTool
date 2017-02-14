/** @file Input/output functions for writing annotation files to the LabelMe server. */

function ReadXML(xml_file,SuccessFunction,ErrorFunction) {
    
  $.ajax({
    type: "GET",
    url: xml_file,
    dataType: "xml",
    success: SuccessFunction,
    error: ErrorFunction
  });
    
}

function ReadVOCXML(Vocxml_file) {
    
    console.log("voc xml:before: "+VocXmlPresent);
    
    $.ajax({
           type: "POST",
           data: { VOCXml: Vocxml_file},
           //dataType: "json",
           //context: document.body,
           url: "http://localhost/LabelMeAnnotationTool/annotationTools/php/xml_transformVOCtoLabelMe.php",
           //success: function(){alert("ok")},
           //error: function(){alert("error")}

           //success: VOCSuccessFunction,
           //error: ReadXML(xml_file,SuccessFunction,ErrorFunction)
           success: function(){VocXmlPresent=true;},
           error: function(){VocXmlPresent=false;},
           async: false
           });
    
           //success: VocXmlPresent=true,
           //error: VocXmlPresent=false
    
    console.log("voc xml:after: "+VocXmlPresent);
    
}

function WriteXML(url,xml_data,SuccessFunction,ErrorFunction) {
    oXmlSerializer =  new XMLSerializer();
    sXmlString = oXmlSerializer.serializeToString(xml_data);
        
    // use regular expressions to replace all occurrences of
    sXmlString = sXmlString.replace(/ xmlns=\"http:\/\/www.w3.org\/1999\/xhtml\"/g, "");
        
    //console.log("write xml - username="+username);
                                    
                        
    $.ajax({
    type: "POST",
    url: url,
    data: sXmlString,
    contentType: "text/xml",
    dataType: "text",
    success: SuccessFunction,
    //success: alert("OK"),
    error: function(xhr,ajaxOptions,thrownError) {
      console.log(xhr.status);          
      console.log(thrownError);
    }
  });
    
    //added by Florian Wirthmüller
    $.ajax({
    type: "POST",
    data: { XmlContent: sXmlString},
    context: document.body,
    //success: alert("VOC_OK"),
    url: "http://localhost/LabelMeAnnotationTool/annotationTools/php/xml_transform.php",
    error: function () {
        alert("!!ERR!!");
           }
  });
                                    
                                    
                                    
}
