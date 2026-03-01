<?php
function hasPermission($permission)
{
    if (!isset($_SESSION['permissions'])) {
        return false;
    }
    return in_array($permission, $_SESSION['permissions']);
}



function requirePermission($permission)
{
    if (!hasPermission($permission)) {
        http_response_code(403);
        exit('Forbidden');
    }
}