<?php 
    class Order{

        protected $product_id;
        protected $user_id;
        protected $id;
        protected $quantity;
        protected $location;

        //setters and getters
        public function setProduct($product_id){
            $this->product_id = $product_id;
        }

        public function getProduct(){
            return $this->product_id;
        }

        public function setQuantity($quantity){
            $this->quantity = $quantity;
        }

        public function getQuantity(){
            return $this->quantity;
        }

        public function setLocation($location){
            $this->location = $location;
        }

        public function getLocation(){
            return $this->location;
        }

        
        public function setUserId($id){
            $this->id = $id;
        }

        public function getUserId(){
            return $this->id;
        }


        public function register($pdo){
            try{
                //hash the pin
                $stmt = $pdo->prepare("INSERT INTO orders 
                (customer_id, product_id, location, quantity) 
                values(?,?,?,?)");
                $stmt->execute([$this->getUserId(),$this->getProduct(),
                                $this->getLocation(), $this->getQuantity() 
                              ]);
            
            }catch(PDOException $e){
                echo $e->getMessage();
            }            
        }

        public function orderedItem($pdo) {
            $stmt = $pdo->prepare("SELECT o.*, p.name AS product_name FROM orders o JOIN products p ON o.product_id = p.id WHERE o.notification = 'Unread' LIMIT 6");
            $stmt->execute();
            $rows = $stmt->fetchAll();
            return $rows;
        }


        public function updateOrder($pdo)
        {
            try {
                //hash the pin
                $notification = 'Read';
                $sql = "UPDATE orders SET notification=? WHERE id=? AND customer_id=?";
                $pdo->prepare($sql)->execute([$notification, $this->getProduct(), $this->getUserId()]);
        
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }
        




    }


?>