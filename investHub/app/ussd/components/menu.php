<?php 
// Imports
include_once '../util.php';
include_once 'user.php';
include_once 'sms.php';
include_once 'product.php';
include_once 'order.php';



class Menu{

    protected $text;
    protected $sessionId;
    
    function __construct(){

    }

    public function middleware($text){
        //remove entries for going back and going to the main menu
        return $this->goBack($this->goToMainMenu($text));
    }

    public function goBack($text){
        $explodedText = explode("*", $text);
        while(array_search(Util::$GO_BACK, $explodedText) != false){
            $firstIndex = array_search(Util::$GO_BACK, $explodedText);
            array_splice($explodedText, $firstIndex-1, 2);
        }
        return join("*", $explodedText);

    }

    public function goToMainMenu($text){
        $explodedText = explode("*", $text);
        while(array_search(Util::$GO_TO_MAIN_MENU, $explodedText) != false){
           $firstIndex = array_search(Util::$GO_TO_MAIN_MENU, $explodedText);
           $explodedText = array_slice($explodedText, $firstIndex + 1); 
        }

        return join("*", $explodedText);
    }



    public function mainMenuRegistered($name){
        $response = "CON Welcome " .$name. " Reply with \n";
        $response .="1. Buy Products\n";
        $response .="2. Notifications\n";
        $response .="3. Sell Products\n";
        $response .="4. My Account\n";
        $response .="5. Help\n";
        echo $response;

    }

    public function mainMenuUnRegistered(){
        $response = "CON Welcome HarvestHub\n";
        $response .="1. Register\n";
        echo $response;
    }

    //registration Menu
    public function registerMenu($textArray, $phoneNumber, $pdo){
        $level = count($textArray);
        if($level == 1){
            echo "CON Enter First Name";
        }else if($level == 2){
            echo "CON Enter Last Name";
        }else if ($level == 3){
            echo "CON Enter Your City";
        }else if ($level == 4){
            echo "CON Enter Your National ID Number";
        }else if ($level == 5){
            echo "CON Set Your PIN (Maximum of 4 Characters)";
        }else if ($level == 6){
            echo "CON Please Re-Enter Your PIN";
        }else if ($level == 7){
            $first_name = $textArray[1];
            $last_name = $textArray[2];
            $city = $textArray[3];
            $national_id = $textArray[4];
            $pin = $textArray[5];
            $confirmPin = $textArray[6];

            if($pin != $confirmPin){
                echo "END Your PIN Do Not Match. Please Try Again";
            }else{
                // register the user 
                $user = new User($phoneNumber);
                $user->setFirstName($first_name);
                $user->setLastName($last_name);
                $user->setCity($city);
                $user->setNationalID($national_id);
                $user->setPin($pin);
                $user->register($pdo);
                // send sms
                $msg = "" .$first_name. " " .$last_name. ", 
                        You Are Now Registered. 
                        Enjoy Our Services ";
                $sms = new Sms($user->getPhone());
                $result = $sms->sendSMS($msg);
                if($result['status'] == "success"){
                    echo "END You will receive an SMS Shortly";
                }else{
                    echo "END Something went wrong. 
                        Please try again";
                }

            } 
            
        }

    }

    // buy products
    public function viewProductMenu($textArray, $name, $id, $pdo, $user){
        $level = count($textArray);
        if($level == 1){
            $numbering = 0;
            $response = "CON Market Place - HarvestHub
                        Choose Product To Buy\n";
            foreach($name as $n){
                $numbering++;
                $n['id'];
                $a=array($numbering,$n['id']);
                $response .=($a[0]).".  " .$n['name']." \n";
                
            }
            $response .=Util::$GO_TO_MAIN_MENU ." Main Menu\n";
            echo $response;
            return $a;

        }else if($level == 2){
            // choosing quantity type
            $numbering = 0;
            $response = "CON Quantity
                         Choose Amount (10kg)\n";
            foreach($type as $t){
                $numbering++;
                $t['id'];
                $a=array($numbering,$t['id']);
                $response .=($a[0])." " .$t['name']." \n";
                
            }
            $response .=Util::$GO_TO_MAIN_MENU ." Main Menu\n";
            echo $response;

        }else if($level == 3){
            echo "CON Enter Drop Location (e.g Area 6)";
        }else if($level == 4){
            $response = "CON Request Details\n";

            $numbering = 0;
            foreach($name as $n){
                $numbering++;
                $a=array($numbering, $n['id']);
                if($textArray[1] == $a[0]){
                    $response .="Product Name : ".$n['name']." \n";
                }
            }

            $counting = 0;
            foreach($type as $t){
                $counting++;
                $a=array($counting, $t['id']);
                if($textArray[2] == $a[0]){
                    $response .="Drop Location : ".$t['name']." \n";
                }
            }

            $response .="Amount: $textArray[2] \n";
            $response .="Drop Location: $textArray[3] \n";
            $response .="1. Confirm\n";
            $response .="2. Cancel\n";
            $response .=Util::$GO_BACK ." Back\n";
            $response .=Util::$GO_TO_MAIN_MENU ." Main Menu\n";
            echo $response;
        }else if($level == 5 && $textArray[4] == 1){
            echo "CON Enter PIN";
        }else if($level == 5 && $textArray[4] == 2){
            echo "END Thank you for using our service";
        }else if($level == 6){
            //echo "END Thank you for using".$textArray[5]." our service";
            $user->setPin($textArray[5]);
            if($user->correctPin($pdo) == true){
                //database serve
                $quantity = $textArray[2];
                $location = $textArray[3];

                $numbering = 0;
                foreach($name as $n){
                    $numbering++;
                    $a=array($numbering, $n['id']);
                    if($textArray[1] == $a[0]){
                        $product_id = $n['id'];
                        $product_name = $n['name'];
                    }
                }

                $order = new Order();
                $order->setProduct($product_id);
                $order->setUserId($id);
                $order->setLocation($location);
                $order->setQuantity($quantity);
                $order->register($pdo);
                date_default_timezone_set('Africa/Blantyre');
                $msg = "Your request has the following details:\n
                        Product Name: $product_name \n 
                        Amount: $quantity\n
                        Location: $location\n
                        Date: ". date("Y-m-d h:i:sa")." \n
                        Enjoy Our Services";
                $sms = new Sms($user->getPhone());
                $result = $sms->sendSMS($msg);
                if($result['status'] == "success"){
                    echo "END You will receive an SMS Shortly";
                }else{
                    echo "END Something went wrong. 
                        Please try again";
                }

            }else{
                $response = "CON Wrong PIN\n";
                $response .=Util::$GO_BACK ." Try Again\n";
                $response .=Util::$GO_TO_MAIN_MENU ." Main Menu\n";
                echo $response;
            }

        }else{
            $response = "CON Wrong Option\n";
            $response .=Util::$GO_BACK ." Bank\n";
            $response .=Util::$GO_TO_MAIN_MENU ." Main Menu\n";
            echo $response;
        }
    }

    public function submitProductMenu($textArray, $id, $pdo, $user){

        $level = count($textArray);
        if($level == 1){
            echo "CON Enter Product Name";
        }else if($level == 2){
            echo "CON Enter Units (e.g Kg)";
        }else if ($level == 3){   
            echo "CON Enter Quantity (250)";
        }else if ($level == 4){   
            echo "CON Enter Selling Price (in MWK) Per Unit";
        }else if ($level == 5){
            $name = $textArray[1];
            $unit = $textArray[2];
            $quantity = $textArray[3];
            $price = $textArray[4];

            if($pin != $confirmPin){
                echo "END Your PIN Do Not Match. Please Try Again";
            }else{
                // register the user 
                $product = new Product();
                $product->setName($name);
                $product->setUnit($unit);
                $product->setQuantity($quantity);
                $product->setPrice($price);
                $product->register($pdo);
                // send sms
                $msg = "You Have Sent the Following Product Details For Apprival" .$name. " " .$unit. " " .$quantity. " " .$price. " , 
                        You Are Now Registered. 
                        Enjoy Our Services ";
                $sms = new Sms($user->getPhone());
                $result = $sms->sendSMS($msg);
                if($result['status'] == "success"){
                    echo "END You will receive an SMS Shortly";
                }else{
                    echo "END Something went wrong. 
                        Please try again";
                }

            } 
            
        }

    }

    public function viewOrdersMenu($textArray, $order ,$id, $pdo, $user, $product, $phoneNumber){
        $level = count($textArray);
        if($level == 1){
            echo "CON Enter PIN";

        }else if($level == 2){
            $user->setPin($textArray[1]);
            if($user->correctPin($pdo) == true){

                $numbering = 0;
                $response = "CON Your Notifications - HarvestHub\n";
                $response .= "1. Orders\n";
                $response .= "2. Submitted Products\n";
                $response .=Util::$GO_TO_MAIN_MENU ." Main Menu\n";
                echo $response;


            }else{
                $response = "CON Wrong PIN\n";
                $response .=Util::$GO_BACK ." Try Again\n";
                $response .=Util::$GO_TO_MAIN_MENU ." Main Menu\n";
                echo $response;
            }
        }else if($level == 3 && $textArray[2] == 1){

            $numbering = 0;
            $response = "CON Your Orders - HarvestHub\n";
            
            $has_orders = false;
            foreach ($order as $n) {
                $numbering++;
                $n['id'];
                $a = array($numbering, $n['id']);
                if ($n['customer_id'] == $id) {
                    $response .= ($a[0]) . ".  " . $n['product_name'] . " " . $n['quantity'] . " " . $n['status'] . " " . $n['updated_at'] . " \n";
                    $has_orders = true;
                }
            }
            
            if (!$has_orders) {
                $response .= "You Have No Orders\n";
            }

            $response .=Util::$GO_TO_MAIN_MENU ." Main Menu\n";
            echo $response;
            return $a;
        }else if($level == 3 && $textArray[2] == 2){
            $numbering = 0;
            $response = "CON Your Submitted Products - HarvestHub\n";
            
            $has_products = false;
            foreach ($product as $n) {
                $numbering++;
                $n['product_id'];
                $a = array($numbering, $n['product_id']);
                if ($n['phone_number'] == $phoneNumber) {
                    $response .= ($a[0]) . ".  " . $n['name'] . " " . $n['unit'] . " " . $n['status'] . " " . $n['created_at'] . " \n";
                    $has_products = true;
                }
            }
            
            if (!$has_products) {
                $response .= "You Have Pending Products\n";
            }

            $response .=Util::$GO_TO_MAIN_MENU ." Main Menu\n";
            echo $response;
            return $a;
        }else if($level == 4){
            if($textArray[2] == 1){

                $numbering = 0;
                foreach($order as $n){
                    $numbering++;
                    $a=array($numbering, $n['id']);
                    if($textArray[3] == $a[0]){
                        $product_id = $n['id'];
                        $product_name = $n['name'];
                    }
                }
                
                $order = new Order();
                $order->setUserId($id);
                $order->setProduct($product_id);
                $order->updateOrder($pdo);
                

                $response = "CON Order Marked Successfully.\n";
                $response .=Util::$GO_BACK ." Mark Another\n";
                $response .=Util::$GO_TO_MAIN_MENU ." Main Menu\n";
                echo $response;

            }else if($textArray[2] == 2){


                $numbering = 0;
                foreach($product as $n){
                    $numbering++;
                    $a=array($numbering, $n['id']);
                    if($textArray[3] == $a[0]){
                        $product_id = $n['id'];
                    }
                }
                
                $product = new Product();
                $product->setID($product_id);
                $product->setPhoneNumber($phoneNumber);
                $product->updateProduct($pdo);

                $response = "CON Product Marked Successfully.\n";
                $response .=Util::$GO_BACK ." Mark Another\n";
                $response .=Util::$GO_TO_MAIN_MENU ." Main Menu\n";
                echo $response;

            }else{

                $response = "CON Wrong Option\n";
                $response .=Util::$GO_BACK ." Try Again\n";
                $response .=Util::$GO_TO_MAIN_MENU ." Main Menu\n";
                echo $response;

            }

                /*
                $numbering = 0;
                foreach($name as $n){
                    $numbering++;
                    $a=array($numbering, $n['id']);
                    if($textArray[1] == $a[0]){
                        $order_id = $n['id'];
                        $product_name = $n['name'];
                        $notification = 'Read';

                    }
                }*/

                //$order->setStatus($id);
                //echo $order->updateOrder($pdo);


        }
        else{
            $response = "CON Wrong Option\n";
            $response .=Util::$GO_BACK ." Try Again\n";
            $response .=Util::$GO_TO_MAIN_MENU ." Main Menu\n";
            echo $response;
        }
    }

    // Account Info
    public function myAccountMenu($textArray,$user,$pdo){
        $level = count($textArray);
        if($level == 1){
            echo "CON Enter Old PIN";
        }else if($level == 2){
            $user->setPin($textArray[1]);
            if($user->correctPin($pdo) == true){
                echo "CON Enter New PIN";
            }else{
                $response = "CON Wrong PIN\n";
                $response .=Util::$GO_TO_MAIN_MENU ." Main Menu\n";
                echo $response;
            }

        }else if ($level == 3){
            echo "CON Confirm PIN";
        }else if ($level == 4){
            $pin = $textArray[2];
            $confirmPin = $textArray[3];
            if($pin != $confirmPin){
                echo "END Your PIN Do Not Match. Please Try Again";
            }else{
                // update pasword the user 
                $user->setPin($pin);
                $user->updatePin($pdo);
                // send sms
                $response = "CON Pin Updated Successfully\n";
                $response .=Util::$GO_TO_MAIN_MENU ." Main Menu\n";
                echo $response;

            } 
        }
        
    }

    public function viewHelp($textArray){
        $level = count($textArray);
        if($level == 1){
            $numbering = 0;
            $response = "CON HarvestHub Help Center
                         Call This Line For Fast Help
                         1. TNM: +265 886 78 82 10
                         2. Airtel: +265 997 59 42 74\n";

            $response .=Util::$GO_TO_MAIN_MENU ." Main Menu\n";
            echo $response;
            return $a;

        }

    }



}


?>