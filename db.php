<?php

$conn = mysqli_connect("localhost", "root", "" , "signin");
if(!$conn){
    die("db connection failed: " . mysqli_connect_error());
}