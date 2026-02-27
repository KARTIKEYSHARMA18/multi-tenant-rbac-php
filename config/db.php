<?php

$conn = mysqli_connect("127.0.0.1", "root", "" , "signin");
if(!$conn){
    die("db connection failed: " . mysqli_connect_error());
}