<?php 

include_once "./components/menu.php";
include_once 'db.php';
include_once './components/user.php';
include_once './components/order.php';
include_once './components/product.php';


//API Variables 
$sessionId = $_POST["sessionId"];
$serviceCode = $_POST["serviceCode"];
$phoneNumber = $_POST["phoneNumber"];
$text = $_POST["text"];


$user = new User($phoneNumber);
$product = new Product();
$order = new Order();
$db = new DBConnector();
$pdo = $db->connectToDB();

//$isRegistered = false;
$menu = new Menu();
$text = $menu->middleware($text);

if($text == "" && $user->isRegistered($pdo)){
    // registered
    $menu->mainMenuRegistered($user->readName($pdo));

}else if($text == "" && !$user->isRegistered($pdo)){
    // unregistered and empty string
    $menu->mainMenuUnRegistered();

}else if(!$user->isRegistered($pdo)){
    // unregistered and non-empty string
    $textArray = explode("*", $text);

    switch($textArray[0]){
        case 1: $menu->registerMenu($textArray, $phoneNumber, $pdo);
        break;
        default: echo "END Invalide Choice. Please
        Try Again";
    }


}else{
    // registered and non-empty string
    $textArray = explode("*", $text);
    switch($textArray[0]){
        case 1: $menu->viewProductMenu($textArray, $product->productNames($pdo), $user->readId($pdo), $pdo, $user);
        break;
        case 2: $menu->viewOrdersMenu($textArray, $order->orderedItem($pdo) , $user->readId($pdo), $pdo, $user, $product->submittedProduct($pdo), $user->readPhoneNumber($pdo));
        break; 
        case 3: $menu->submitProductMenu($textArray, $user->readId($pdo), $pdo, $user);
        break;
        case 4: $menu->myAccountMenu($textArray, $user, $pdo);
        break;
        case 5: $menu->viewHelp($textArray);
        break;
        default: echo "END Invalide Choice. Please
        Try Again";
    }
}


?>