<?php 
    require './vendor/autoload.php';
    use AfricasTalking\SDK\AfricasTalking;


    include_once '../util.php';
    include_once '../db.php';


    class Sms{
        protected $phone_number;
        protected $AT;

        function __construct($phone_number){
            $this->phone_number = $phone_number;
            $this->AT = new AfricasTalking(Util::$API_USERNAME, Util::$API_KEY);
        }

        public function getPhone(){
            return $this->phone_number;
        }

        public function sendSMS($message){
            //get the sms service
            $sms = $this->AT->sms();
            //use the service

            $result = $sms->send([
                'to'    => $this->getPhone(),
                'message' => $message,
                'from' => Util::$COMPANY_NAME
            ]);

            return $result;
        }

        public function sendText($message){
            //get the sms service
            $sms = $this->AT->sms();
            //use the service

            $result = $sms->send([
                'to'    => $this->getPhone(),
                'message' => $message,
                'from' => Util::$SMS_SHORTCODE
            ]);

            return $result;
        }
    }


?>