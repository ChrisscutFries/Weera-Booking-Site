<script src="js/sweetalert.min.js"></script>

<?php
if(isset($_POST['submitbtn'])){
    $to = "christopherandre.malilay.cics@ust.edu.ph";
    $name = $_POST['name'];
    $email = $_POST['email'];
    $number = $_POST['number'];
    $package = $_POST['package'];
    $students = $_POST['students'];
    $date = $_POST['date'];
    $subject = "New Booking for $date, from $name!";
    $message = $_POST['message'];

    $email_body = "Name: $name\n\nEmail: $email\n\nNumber: $number\n\nPackage: $package\n\nNumber of Students: $students\n\nDate: $date\n\nMessage:\n$message";

    // Send email
    mail($to, $subject, $email_body);
}
?>


<?php
    function checkexistingname($name,$conn,$email) {
        $query = mysqli_query($conn,"SELECT * FROM customer_fact_table");      //Checks for existing name
        while ($result = mysqli_fetch_array($query)){
            if($name === $result["Name"] && $email === $result["EMAIL"]){
                return true;
            }
        }
        return false;
    }

    function getidN($name,$conn,$email) {
        $query = mysqli_query($conn,"SELECT * FROM customer_fact_table");      //return the Cust_ID for same name in Database
        while ($result = mysqli_fetch_array($query)){
            if($name === $result["Name"] && $email === $result["EMAIL"]){
                return $result["Cust_ID"];
            }
        }
    }

    function checkexistingemail($email,$conn) {
        $query = mysqli_query($conn,"SELECT * FROM customer_fact_table");      //Checks for existing email
        while ($result = mysqli_fetch_array($query)){
            if($email === $result["EMAIL"]){
                return true;
            }
        }
        return false;
    }

    function getidE($email,$conn) {
        $query = mysqli_query($conn,"SELECT * FROM customer_fact_table");      //return the Cust_ID for same email in Database
        while ($result = mysqli_fetch_array($query)){
            if($email === $result["EMAIL"]){
                return $result["Cust_ID"];
            }
        }
    }

    function checkexistingPackage($package,$conn) {
        $query = mysqli_query($conn,"SELECT * FROM package_table");      //checks for existing package
        while ($result = mysqli_fetch_array($query)){
            if($package === $result["Pack_Name"]){
                return true;
            }
        }
        return false;
        }

    function getidP($package,$conn) {
    $query = mysqli_query($conn,"SELECT * FROM package_table");      //return the Pack_ID for same package in Database
        while ($result = mysqli_fetch_array($query)){
            if($package === $result["Pack_Name"]){
                return $result["Package_ID"];
            }
        }
    }

    function getcost($package){
        if($package == "Private Solo Session"){
            return 450;
        }if ($package == "Private Group Session"){
            return 400;
        } if ($package == "Group Class (1 Session)"){
            return 350;
        } if ($package == "Group Class (3 Sessions)"){
            return 1000;
        } if ($package == "Group Class (6 Sessions)"){
            return 2000;
        } if ($package == "Group Class (9 Sessions)"){
            return 3000;
        } if ($package == "Group Class (12 Sessions)"){
            return 4000;
        }
    }
    
    function getdur($package){
        if($package == "Private Solo Session" || $package ==  "Private Group Session"){
            return "2 hours";
        } else {
            return "1.5 hours";
        }
    }

    function createdb(){
    $host = "localhost";
    $dbname = "mysql";
    $username = "root";
    $password = "";
    $conn1 = mysqli_connect(hostname: $host,
                username: $username,
                password: $password,
                database: $dbname);

    $sql= "CREATE DATABASE IF NOT EXISTS weeradb"; 
    mysqli_query($conn1,$sql);
    }

include("index.html");
createdb();                //Creation of weeradbclean
$host = "localhost";
$dbname = "weeradb";
$username = "root";
$password = ""; 

$conn = mysqli_connect(hostname: $host,
                username: $username,
                password: $password,
                database: $dbname);

$sql= "CREATE TABLE IF NOT EXISTS customer_fact_table (
    Cust_ID int AUTO_INCREMENT NOT NULL,
    Name VARCHAR(60) NOT NULL,
    CP_NUM VARCHAR(11) NOT NULL,
    EMAIL VARCHAR(60) NOT NULL,
    Reason text,
    PRIMARY KEY (Cust_ID))";
    mysqli_query($conn,$sql);

$sql= "CREATE TABLE IF NOT EXISTS package_table (
    Package_ID int AUTO_INCREMENT NOT NULL,
    Pack_Name VARCHAR(255) NOT NULL,
    Duration VARCHAR(9) NOT NULL,
    Cost int(4) NOT NULL,
    PRIMARY KEY (Package_ID))";
    mysqli_query($conn,$sql);

$sql= "CREATE TABLE IF NOT EXISTS book_fact_table (
    Book_ID int AUTO_INCREMENT NOT NULL,
    Cust_ID int,
    Package_ID int,
    Ses_Date date NOT NULL,
    Num_Students int(1) NOT NULL,
    PRIMARY KEY (Book_ID),
    FOREIGN KEY (Cust_ID) REFERENCES customer_fact_table(Cust_ID),
    FOREIGN KEY (Package_ID) REFERENCES package_table(Package_ID))";
    mysqli_query($conn,$sql);

    
include 'index.html';
if(isset($_POST['submitbtn'])){
$name =  $_POST["name"];
$email =  $_POST["email"];
$number =  $_POST["number"];
$package = $_POST["package"];
$students =  $_POST["students"];
$date = $_POST["date"];
$reason =  $_POST["message"];
$cost = getcost($package);
$duration = getdur($package);

$sql= "INSERT INTO customer_fact_table(Name, CP_NUM , Email , Reason)
        VALUES (? ,? ,? ,?)";   //sql command for inserting data to Customer fact table

$stmt= mysqli_stmt_init($conn);
if (!mysqli_stmt_prepare($stmt, $sql)){
    die (mysqli_error($conn));
}                                            //checks if may error!
mysqli_stmt_bind_param($stmt, "ssss", $name, $number, $email, $reason);   //prevents html tags to affect the input of users
mysqli_stmt_execute($stmt);

$sql2= "INSERT INTO package_table(Pack_Name, Duration, Cost)
        VALUES ('$package', '$duration', '$cost')";   //sql command for inserting data to Package fact table
        mysqli_query($conn,$sql2);

if (checkexistingname($name,$conn,$email) && checkexistingPackage($package,$conn)){   //If the person that has same name and email = "SAME PERSON"
    $id1=getidN($name,$conn,$email);
    $id2=getidP($package,$conn);
    mysqli_query($conn,"INSERT INTO book_fact_table(Cust_ID, Package_ID, Ses_Date, Num_Students) VALUES ('$id1','$id2','$date','$students')");
} else if (checkexistingemail($email,$conn) == false && checkexistingPackage($package,$conn) == true){   //If the person has same name but different email = "DIFF PERSON"
    $sql= "SELECT count(Cust_ID) AS total FROM customer_fact_table";
    $result= mysqli_query($conn,$sql);
    $values=mysqli_fetch_assoc($result);
    $add = (int)$values['total'];
    $id1= $add+1;
    $id2=getidP($package,$conn);
    mysqli_query($conn,"INSERT INTO book_fact_table(Cust_ID, Package_ID, Ses_Date, Num_Students) VALUES ('$id1','$id2','$date','$students')");
} else {                                                                                   //If the email and name does not exist in database simply add it to record
    $sql= "SELECT count(Cust_ID) AS total FROM customer_fact_table";
    $result= mysqli_query($conn,$sql);
    $values=mysqli_fetch_assoc($result);
    $add = (int)$values['total'];
    $id1= $add+1;
    $sql= "SELECT count(Package_ID) AS total FROM package_table";
    $result= mysqli_query($conn,$sql);
    $values=mysqli_fetch_assoc($result);
    $add = (int)$values['total'];
    $id2 = $add+1;
    mysqli_query($conn,"INSERT INTO book_fact_table(Cust_ID, Package_ID, Ses_Date, Num_Students) VALUES ('$id1','$id2','$date','$students',)");
}
    header('Location: index.html');
} else {
    die();
}
?>

<script>
    swal({
        title:"Your Form Has Been Submitted",
        text: "Thank you for your patronage",
        icon: "success",
    })
    </script>