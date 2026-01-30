<?php
class Employee{
    public $name;
    private $salary;
    
    function __construct($name,$salary){
        $this->name=$name;
        $this->salary=$salary;
    }
}
$employee=new Employee("Riddhi",500000);
print_r ($employee);
?>

