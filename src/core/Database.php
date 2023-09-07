<?php

function getConnection()
{
    return new pdo('mysql:host=' . $_SERVER['DB_HOST'] . ';dbname=' . $_SERVER['DB_NAME'], $_SERVER['DB_USER'], $_SERVER['DB_PASSWORD']);
} // létre jön a kapcsolat az adatbázissal egy .htacces fileon keresztül


?>