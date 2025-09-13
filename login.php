<?php
session_start();
if (isset($_SESSION['user_id'])) header("Location: user/user.php");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/auth.css">
    <script src="jquery.min.js"></script>
</head>
<body>
<div class="login-container">
    <h2>Login</h2>
    <div id="msg"></div>
    <form id="loginForm">
        <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
        <input type="hidden" name="login_user" value="1">
        <button type="submit" class="btn-primary">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Sign up</a></p>
</div>

<script>
$("#loginForm").submit(function(e){
    e.preventDefault();
    var datas = $(this).serializeArray();
    var data_array = {};
    $.map(datas, function (data) { data_array[data['name']] = data['value']; });
    $.ajax({
        url: "user/user_request.php",
        method: "POST",
        data: data_array,
        success: function(res){
            if (res.trim().toLowerCase() === "success") {
                window.location.href = "user/user.php";
            } else {
                alert(res);
            }
        },
        error: function(){ alert("localhost: Something went wrong while logging in."); }
    });
});
</script>
</body>
</html>
