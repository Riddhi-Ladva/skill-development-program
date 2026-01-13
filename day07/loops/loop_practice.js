const prompt = require("prompt-sync")();
//forloop practice
// const mark=[10,20,30,40];

// for(let i=0;i<mark.length;i++)
// {
//     mark[i]+=10;
// }

// console.log(mark);

// //whileloop
// const prompt = require("prompt-sync")();

// let enteredPin="";
// let correctPin="1234";
// while(enteredPin !== correctPin)
// {
//     enteredPin=prompt("Enter your ATM pin");
//     if(enteredPin !== correctPin)
//     {
//     console.log("Access denied");
//     }
//     else{
//         console.log("Access granted");
//         break;
//     }
// }

//Do While loop

// let playAgain="";

// do{
//     console.log("Playing Game👾");
//     playAgain=prompt("Play Again: y/n ?");

// }while(playAgain==="y")

// console.log("Game over");

//labeled break

// searchStudent:
// for(let classno=1;classno<11;classno++)
// {
//     console.log("classno:",classno)
//     for(let rollno=1;rollno<20;rollno++)
//     {
//         console.log("rollno:",rollno)
//         if(classno==2 && rollno==18)
//         {
//             console.log("Student Found!")
//             break searchStudent;
//         }
//     }
// }

//continue
const peoples=["roshni","rahul","staff","suhani"]
checking:
for(let i=0;i<peoples.length;i++)
{
    if(peoples[i]=="staff")
    {
        console.log("Give granted to staff");
        continue checking;
    }
    console.log("check passport",peoples[i]);
    console.log("check bags",peoples[i]);
}