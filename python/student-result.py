


class student:
    def __init__(self,name,marks):
        self.name = name
        self.__marks = marks
    
    def __result(self,average):
        if average >= 90:
            print(self.name +"-"+"Average: "+str(average)+" - Grade: A+")
            f = open("results.txt", "a")
            f.write(self.name +"-"+"Average: "+str(average)+" - Grade: A+\n")
            f.close()
        elif average >= 80:
            print(self.name +"-"+"Average: "+str(average)+" - Grade: B")
            f = open("results.txt", "a")    
            f.write(self.name +"-"+"Average: "+str(average)+" - Grade: B\n")
            f.close()
        elif average >= 70:
            print(self.name +"-"+"Average: "+str(average)+" - Grade: C")
            f = open("results.txt", "a")
            f.write(self.name +"-"+"Average: "+str(average)+" - Grade: C\n")
            f.close()
        elif average >= 60:
            print(self.name +"-"+"Average: "+str(average)+" - Grade: D")
            f = open("results.txt", "a")
            f.write(self.name +"-"+"Average: "+str(average)+" - Grade: D\n")
            f.close()
        else:
            print(self.name +"-"+"Average: "+str(average)+" - Grade: F")
            f = open("results.txt", "a")
            f.write(self.name +"-"+"Average: "+str(average)+" - Grade: F\n")
            f.close()

    def calculate_average(self):
        average = sum(self.__marks) / len(self.__marks)
        
        self.__result(average)

    

name = input("Enter student's name: ")
marks = list(map(int, input("Enter marks separated by space: ").split()))
new_student = student(name, marks)
# new_student.__result() AttributeError: 'student' object has no attribute '__result'
new_student.calculate_average()
