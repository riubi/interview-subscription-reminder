<?php

function db_connect($db_name)
{
    $db = new mysqli('host', 'user', 'password', $db_name);
    register_shutdown_function(function () use ($db) {
        $db->close();
    });

    return $db;
}
