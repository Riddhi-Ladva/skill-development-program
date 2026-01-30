<?php
class User {
    private $name;
    private $email;

    public function __construct($name, $email) {
        $this->name  = $name;
        $this->email = $email;
    }

    // Called when object is echoed
    public function __toString(): string {
        return json_encode([
            "name"  => $this->name,
            "email"=> $this->email
        ]);
    }

    // Called when accessing undefined property
    public function __get($property) {
        return "The property '{$property}' does not exist.";
    }
}

// Object creation
$user = new User("Riddhi", "riddhi@gmail.com");

// __toString() called here
echo $user . "\n";

// __get() called here
echo $user->address;
?>
