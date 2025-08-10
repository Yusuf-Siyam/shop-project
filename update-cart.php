<?php
session_start();
include("connect.php");

// Check if user is logged in
if(!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Check if required parameters are present
if(!isset($_GET['id']) || !isset($_GET['action'])) {
    header("Location: shop.php");
    exit();
}

$cartId = $_GET['id'];
$action = $_GET['action'];
$userId = $_SESSION['user_id'];

// Verify cart item belongs to current user
$checkCartQuery = "SELECT * FROM cart WHERE id = $cartId AND user_id = $userId";
$checkCartResult = mysqli_query($conn, $checkCartQuery);

if(mysqli_num_rows($checkCartResult) > 0) {
    $cartItem = mysqli_fetch_assoc($checkCartResult);
    
    switch($action) {
        case 'increase':
            // Increase quantity by 1
            $newQuantity = $cartItem['quantity'] + 1;
            mysqli_query($conn, "UPDATE cart SET quantity = $newQuantity WHERE id = $cartId");
            break;
            
        case 'decrease':
            // Decrease quantity by 1, remove if quantity becomes 0
            $newQuantity = $cartItem['quantity'] - 1;
            if($newQuantity > 0) {
                mysqli_query($conn, "UPDATE cart SET quantity = $newQuantity WHERE id = $cartId");
            } else {
                mysqli_query($conn, "DELETE FROM cart WHERE id = $cartId");
            }
            break;
            
        case 'remove':
            // Remove item from cart
            mysqli_query($conn, "DELETE FROM cart WHERE id = $cartId");
            break;
    }
}

// Redirect back to referring page
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'shop.php';
header("Location: $referer");
exit();
?> 