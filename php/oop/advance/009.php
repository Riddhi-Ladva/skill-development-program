<?php
class Employee {
    public $name;
    protected $salary;

    function __construct($name, $salary) {
        $this->name = $name;
        $this->salary = $salary;
    }

    public function getDetails() {
        return "Name: " . $this->name . ", Salary: " . $this->salary;
    }
}

class Manager extends Employee {
    private $department;

    function __construct($name, $salary, $department) {
        parent::__construct($name, $salary);
        $this->department = $department;
    }

    public function getDetails() {
        return "Name: " . $this->name .
               ", Salary: " . $this->salary .
               ", Department: " . $this->department;
    }
}

$manager = new Manager("Harsh", 800000, "Sales");
echo "Manager Details\n";
echo $manager->getDetails();

echo "\n\n";

$employee = new Employee("Riddhi", 500000);
echo "Employee Details\n";
echo $employee->getDetails();
?>
