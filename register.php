<?php
session_start();
if (isset($_SESSION['user_id'])) header("Location: user/user.php");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Register</title>
    <link rel="stylesheet" href="assets/css/auth.css">
    <script src="jquery.min.js"></script>
</head>
<body>
<div class="login-container">
    <h2>Register</h2>
    <div id="msg"></div>
    <form id="regForm">
        <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
        <div class="form-group"><label>Password</label><input id="pwd" type="password" name="password" required></div>
        <div class="form-group"><label>Confirm</label><input type="password" id="pwd2" required></div>
        <input type="hidden" name="register_user" value="1">
        <button type="submit">Register</button>
    </form>
    <p>Already have account? <a href="login.php">Login</a></p>
</div>

<script>
$("#regForm").submit(function(e){
    e.preventDefault();
    var p = $("#pwd").val(), p2 = $("#pwd2").val();
    if (p !== p2) { alert("Passwords do not match."); return; }
    var datas = $(this).serializeArray();
    var data_array = {};
    $.map(datas, function (data) { data_array[data['name']] = data['value']; });
    $.ajax({
        url: "user/user_request.php",
        method: "POST",
        data: data_array,
        success: function(res){
            if (res.trim().toLowerCase() === "success") {
                alert("Registered. You may now log in.");
                window.location.href = "login.php";
            } else {
                alert(res);
            }
        }, error: function(){ alert("localhost: Something went wrong while registering."); }
    });
});
</script>
</body>
</html>
