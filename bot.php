<?php
    


    $bot_url = "https://api.telegram.org/bot817829832:AAE0WAC1cFrWPqw6bjVAtiB08WlPHn_00BQ";
   
   
    $update = file_get_contents("php://input");
    
    $update_array = json_decode($update, true);
    
    if( isset($update_array["message"]) ) {
        
        $text    = $update_array["message"]["text"];
        $chat_id = $update_array["message"]["chat"]["id"];
    }
     
    //-------------------------------------
    
    $key1 = 'دریافت لیست محصولات';

    $reply_keyboard = [
                          [$key1]
                      ];
                      
    $reply_kb_options = [
                            'keyboard'          => $reply_keyboard ,
                            'resize_keyboard'   => true ,
                            'one_time_keyboard' => false ,
                        ];
    
    //-------------------------------------
    
    switch($text) {
        
        case "/start" : show_menu();      break;
        case $key1    : show_products();  break;
    }
    
    //-------------------------------------
    
    function show_menu() {
        
        $json_kb = json_encode($GLOBALS['reply_kb_options']);
        $reply = "گزینه مورد نظر خود را انتخاب کنید";
        $url = $GLOBALS['bot_url'] . "/sendMessage";
        $post_params = [ 'chat_id' =>  $GLOBALS['chat_id'] , 'text' => $reply , 'reply_markup' => $json_kb ];
        send_reply($url, $post_params);
    }
    
    //-------------------------------------
    
    function show_products() {
        
        $connection = connect_to_db();
        
        $result = $connection -> query("SELECT * FROM products");
        
        while($row = $result -> fetch_assoc()) {
         
            $id        = $row['id'];
            $name      = $row['name'];
            $price     = $row['price'];
            $image_url = $row['image_url'];
            
            $reply  = $name . "\n" . $price . " تومان" . "\n\n";
            
            $reply .= "/edit"   . $id . "  👈  " . "ویرایش" . "\n";
            $reply .= "/delete" . $id . "  👈  " . "حذف"    . "\n\n";
            
            $url = $GLOBALS['bot_url']."/sendPhoto";
    	    $post_params = [ 
    	                        'chat_id' => $GLOBALS['chat_id'] , 
    	                        'photo'   => new CURLFile(realpath($image_url)),
    	                        'caption' => $reply
    	                   ];
    	    send_reply($url, $post_params);
        }
        
        $connection -> close();
        
        //---------------------
        
        $inline_keyboard = [
                                [
                                    [ 'text' => "ثبت محصول جدید" , 'callback_data' => "add_new_product" ]
                                ]
                           ];
    
        $inline_kb_options = [
                                'inline_keyboard' => $inline_keyboard
                             ];
        
        $json_kb = json_encode($inline_kb_options);  
        $reply   = "👇جهت افزودن محصول جدید دکمه زیر را لمس کنید👇";
        $url = $GLOBALS['bot_url']."/sendMessage";
    	$post_params = [ 'chat_id' => $GLOBALS['chat_id'] , 'text' => $reply , 'reply_markup' => $json_kb ];
    	send_reply($url, $post_params);
    }

    //-------------------------------------
    
    function connect_to_db() {

        $connection = new mysqli("localhost", "semilea1_user", "Bot123456789", " semilea1_botdb");
        
        if ($connection -> connect_error)
            echo "Failed to connect to db: " . $connection -> connect_error;
            
        $connection -> query("SET NAMES utf8");
        
        return $connection;
    }
    
    //-------------------------------------
    
    function send_reply($url, $post_params) {
        
        $cu = curl_init();
        curl_setopt($cu, CURLOPT_URL, $url);
        curl_setopt($cu, CURLOPT_POSTFIELDS, $post_params);
        curl_setopt($cu, CURLOPT_RETURNTRANSFER, true);  // get result
        $result = curl_exec($cu);
        curl_close($cu);
        return $result;
    }

   
?>
