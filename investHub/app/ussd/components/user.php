<?php
class User
{

    protected $first_name;
    protected $last_name;
    protected $city;
    protected $id;
    protected $pin;
    protected $phonenumber;
    protected $national_id;

    protected $help_description;

    //Product Variables
    protected $name;
    protected $unit;
    protected $price;
    protected $quantity;

    public function __construct($phonenumber)
    {
        $this->phonenumber = $phonenumber;
    }

    //setters and getters
    public function setHelp($help_description)
    {
        $this->help_description = $help_description;
    }

    public function getHelp()
    {
        return $this->help_description;
    }

    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
    }

    public function getFirstName()
    {
        return $this->first_name;
    }

    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
    }

    public function getLastName()
    {
        return $this->last_name;
    }

    public function setCity($city)
    {
        $this->city = $city;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setPin($pin)
    {
        $this->pin = $pin;
    }

    public function getPin()
    {
        return $this->pin;
    }

    public function setNationalID($national_id)
    {
        $this->national_id = $national_id;
    }

    public function getNationalID()
    {
        return $this->national_id;
    }

    public function setUserID($id)
    {
        $this->id = $id;
    }

    public function getUserID()
    {
        return $this->id;
    }

    public function getPhone()
    {
        return $this->phonenumber;
    }

    // product getters and setters
    public function setProductQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    public function getProductQuantity()
    {
        return $this->quantity;
    }

    public function setProductPrice($price)
    {
        $this->price = $price;
    }

    public function getProductPrice()
    {
        return $this->price;
    }

    public function setProductName($name)
    {
        $this->name = $name;
    }

    public function getProductName()
    {
        return $this->name;
    }

    public function setProductUnit($unit)
    {
        $this->unit = $unit;
    }

    public function getProductUnit()
    {
        return $this->unit;
    }

    public function register($pdo)
    {
        try {
            //hash the pin
            $hashedPin = password_hash($this->pin, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users
            (first_name, last_name,pin, city, phone_number, national_id, password)
                                   values(?,?,?,?,?,?,?)");
            $stmt->execute([$this->getFirstName(), $this->getLastName(), $hashedPin, $this->getCity(),
                $this->getPhone(), $this->getNationalID(), $hashedPin]);

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function submitProduct($pdo)
    {
        try {
            $stmt = $pdo->prepare("INSERT INTO products
            (name, unit, price, quantity, phone_number)
                                   values(?,?,?,?,?)");
            $stmt->execute([$this->getProductName(), $this->getProductUnit(), $this->getProductPrice(), $this->getProductQuantity(),
                $this->getPhone()]);

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function readName($pdo)
    {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE phone_number=?");
        $stmt->execute([$this->getPhone()]);
        $row = $stmt->fetch();
        return $row['first_name'];

    }

    public function readPhoneNumber($pdo)
    {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE phone_number=?");
        $stmt->execute([$this->getPhone()]);
        $row = $stmt->fetch();
        return $row['phone_number'];

    }

    public function readId($pdo)
    {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE phone_number=?");
        $stmt->execute([$this->getPhone()]);
        $row = $stmt->fetch();
        return $row['id'];

    }

    public function isRegistered($pdo)
    {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE phone_number=?");
        $stmt->execute([$this->getPhone()]);

        if (count($stmt->fetchAll()) > 0) {
            return true;
        } else {
            return false;
        }

    }

    public function correctPin($pdo)
    {

        $stmt = $pdo->prepare("SELECT pin FROM users WHERE phone_number=?");
        $stmt->execute([$this->getPhone()]);
        $row = $stmt->fetch();

        if ($row == null) {
            return false;
        }

        if (password_verify($this->getPin(), $row['pin'])) {
            return true;
        }

        return false;

    }

    public function updatePin($pdo)
    {
        try {
            //hash the pin
            $hashedPin = password_hash($this->pin, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET pin=? WHERE phone_number=?";
            $pdo->prepare($sql)->execute([$hashedPin, $this->getPhone()]);

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

}
