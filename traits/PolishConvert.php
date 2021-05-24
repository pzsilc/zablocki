<?php

trait PolishConvert
{
    public function polish_convert_body()
    {
        $class = get_called_class();
        if($class === 'Order' && $this->id > 1503)
            return $this->message;
        elseif($class === 'Comment' && $this->id > 18462)
            return $this->content;

        $msg = $class === 'Order' ? $this->message : $this->content;
        $msg=str_replace("Ä…","ą",$msg);
        $msg=str_replace("Åº","ć",$msg);
        $mgs=str_replace('Ä‡','ć',$msg);
        $msg=str_replace("Ä™","ę",$msg);
        $msg=str_replace("Ăł","ó",$msg);
        $msg=str_replace("Ĺ‚","ł",$msg);
        $msg=str_replace("Ĺ›","ś",$msg);
        $msg=str_replace("Ĺź","ż",$msg);
        $msg=str_replace("Ĺş","ź",$msg);
        $msg=str_replace("Å","Ł",$msg);
        $msg=str_replace("Ä˜","Ę",$msg);
        $msg=str_replace("Ă“","Ó",$msg);
        $msg=str_replace("Ăź","ü",$msg);
        $msg=str_replace("Ă¤","ä",$msg);
        $msg=str_replace("Ĺ‘","ö",$msg);
        $msg=str_replace("Ĺ","Ö",$msg);
        $msg=str_replace("Å‚","ł",$msg);
        $msg=str_replace('Å›','ś',$msg);
        $msg=str_replace('Ã³','ó',$msg);
        $msg=str_replace('Å¼','ż',$msg);
        $msg=str_replace('Å„','ń',$msg);
        $msg=str_replace('Ã“','Ó',$msg);
        $msg=str_replace('Å»','Ż',$msg);
        return $msg;
    }
}

?>