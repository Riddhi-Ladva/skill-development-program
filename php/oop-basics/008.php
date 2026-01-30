<?php

class Employee{
    public $name;
    private $salary;
    
    function __construct($name,$salary){
        $this->name=$name;
        $this->salary=$salary;
    }

    public function getSalary(){
        return $this->salary;
    }
    
    public function setSalary($amount) { 
    if ($amount > 0) { 
        $this->salary = $amount; 
    } else { 
        echo "Invalid salary amount."; 
    } 
} 

}
$employee=new Employee("Riddhi",500000);
echo "\nSalary: " . $employee->getSalary();

$employee->setSalary(600000);
echo "\nUpdated Salary: " . $employee->getSalary();
?>

