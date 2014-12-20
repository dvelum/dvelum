<?php
   require_once '../../www/system/library/Image/Resize.php';
    
   $images = array('1.jpg','2.jpg','3.jpg','4.jpg','5.jpg','6.jpg');
   $sizes = array(
       array(300,100),
       array(300,250),
       array(400,100),
       array(400,350),
       array(200,200),
       array(100,300)
   );
   
   foreach ($images as $image)  
       foreach ($sizes as $k=>$config)
          Image_Resize::resize('./src/'.$image , $config[0] , $config[1] , './thumbs/crop_'.$image.'_'.$config[0].'x'.$config[1].'.jpg' , false , true);
          
          
    foreach ($images as $image)  
       foreach ($sizes as $k=>$config)
          Image_Resize::resize('./src/'.$image , $config[0] , $config[1] , './thumbs/simple_'.$image.'_'.$config[0].'x'.$config[1].'.jpg' , false , false);      

   
    foreach ($images as $image)  
       foreach ($sizes as $k=>$config)
          Image_Resize::resize('./src/'.$image , $config[0] , $config[1] , './thumbs/simple_fit_'.$image.'_'.$config[0].'x'.$config[1].'.jpg' , true , false); 
?>