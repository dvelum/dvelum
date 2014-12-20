<?php
/**
 * Wrapper for Zend_Mail can attach inline images
 * @use Zend_Mail
 */
class Mail extends Zend_Mail
{
    /**
     * Parce httml attach images
     */
    public function buildHtml()
    {
        // Important, without this line the example don't work!
        // The images will be attached to the email but these will be not
        // showed inline
        $this->setType(Zend_Mime::MULTIPART_RELATED);
    
        $zend_mime_part = $this->getBodyHtml();
        $html = quoted_printable_decode($zend_mime_part->getContent());
        
        $matches = array();
        preg_match_all("#<img.*?src=['\"]([^'\"]+)#i", $html, $matches);
   
        $matches = array_unique($matches[1]);

        if (count($matches ) > 0) {
            foreach ($matches as $key => $filename) {
                if (is_readable($filename)) {
                    $at = new Zend_Mime_Part(file_get_contents($filename));
                    $at->type = $this->mimeByExtension($filename);
                    $at->disposition = Zend_Mime::DISPOSITION_INLINE;
                    $at->encoding = Zend_Mime::ENCODING_BASE64;
                    $at->id = 'cid_' . md5_file($filename);
                    $html = str_replace( $filename,'cid:' . $at->id,$html);
                    $this->addAttachment($at);
                }
            }
            $this->setBodyHtml($html , 'UTF-8' ,Zend_Mime::ENCODING_8BIT);
        }
    }
    /**
     * Simple mime detector
     * @param string $filename
     * @return string
     */
    public function mimeByExtension($filename)
    {
        if (is_readable($filename) ) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            switch ($extension) {
            	case 'gif':
            	    $type = 'image/gif';
            	    break;
            	case 'jpg':
            	case 'jpeg':
            	    $type = 'image/jpg';
            	    break;
            	case 'png':
            	    $type = 'image/png';
            	    break;
            	default:
            	    $type = 'application/octet-stream';
            }
        }
    
        return $type;
    }
}