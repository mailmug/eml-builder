<?php 

namespace Mailmug\EmlBuilder;

class EmlBuilder{

    public static function guid(){
        $len = 32; //32 bytes = 256 bits
        $bytes = '';
        if (function_exists('random_bytes')) {
            try {
                $bytes = random_bytes($len);
            } catch (\Exception $e) {
                //Do nothing
            }
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            /** @noinspection CryptographicallySecureRandomnessInspection */
            $bytes = openssl_random_pseudo_bytes($len);
        }
        if ($bytes === '') {
            //We failed to produce a proper random string, so make do.
            //Use a hash to force the length to the same as the other methods
            $bytes = hash('sha256', uniqid((string) mt_rand(), true), true);
        }

        //We don't care about messing up base64 format here, just want a random string
        return str_replace(['=', '+', '/'], '', base64_encode(hash('sha256', $bytes, true)));
    }
   
    public static function toEmailAddress( $data ){
        $email = "";
        if(is_string( $data ) ) {
          $email = $data;
        }if ( is_array( $data )) {
            $max = count($data);
            for ($i = 0; $i < $max; $i++) {
                $email .= (strlen($email) > 0 ? ', ' : '');
                if ($data[$i]->name) {
                    $email .= '"' . $data[$i]->name . '"';
                }
                if ($data[$i]->email) {
                    $email .= (strlen($email) > 0 ? ' ' : '') . '<' . $data[$i]->email . '>';
                }
            }
        }elseif( is_object($data)) {
            if (isset( $data->name ) ) {
                $email .= '"' . $data->name . '"';
            }
            if (isset( $data->email )) {
                $email .= (strlen($email) > 0  ? ' ' : '') . '<' . $data->email . '>';
            }
        }
        return $email;
    }


    public static function getBoundary( $contentType ){
        $regex = '/boundary="?(.+?)"?(\s*;[\s\S]*)?$/im';
        preg_match_all($regex, $contentType, $match );
        return isset($match[1][0]) ? $match[1][0] : null;
    }


    public static function build( Object $data )
    {
        $eml = "";
        $EOL = "\r\n"; //End-of-line

        if( empty( $data->headers ) ) {
            $data->headers = [];
        }
          
        if ( is_string( $data->subject )) {
            $data->headers["Subject"] = $data->subject;
        }
          
        if ( !empty( $data->from ) ) {
            $data->headers["From"] = ( is_string( $data->from ) ? $data->from : self::toEmailAddress($data->from));
        }
          
        if( !empty( $data->to ) ) {
            $data->headers["To"] = (is_string( $data->to ) ? $data->to : self::toEmailAddress($data->to));
        }
          
        if ( !empty( $data->cc ) ) {
            $data->headers["Cc"] = (is_string( $data->cc ) ? $data->cc : self::toEmailAddress($data->cc));
        }
        
        if ( empty( $data->headers["To"] ) ) {
            throw new \Error("Missing 'To' e-mail address!");
        }
          
        $boundary = "----=" . self::guid();
        if (empty( $data->headers["Content-Type"] ) ) {
            $data->headers["Content-Type"] = 'multipart/mixed;' . $EOL . 'boundary="' . $boundary . '"';
        }else{
            $name = self::getBoundary( $data->headers["Content-Type"] );
            if (!empty( $name )) {
              $boundary = $name;
            }
        }

        //Build headers
        foreach( $data->headers as $key => $value){
            if (empty( $value)) {
                continue; //Skip missing headers
            }elseif (is_string( $value )) {
                $eml .= $key . ": " . preg_replace("/\r?\n/m", $EOL . "  ", $value) . $EOL;
            }else { //Array
                foreach($value as $k => $val) {
                    $eml .= $k + ": " . preg_replace("/\r?\n/m", $EOL . "  ", $val) . $EOL;
                }
            }
        }
    
        //Start the body
        $eml .= $EOL;
        
        //Plain text content
        if($data->text) {
            $eml .= "--" . $boundary . $EOL;
            $eml .= "Content-Type: text/plain; charset=utf-8" . $EOL;
            $eml .= $EOL;
            $eml .= $data->text;
            $eml .= $EOL . $EOL;
        }

        echo $eml;
        
    }
}