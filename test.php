<?php

include "user.php";
include "db.php";

session_start();

function sanitize($str){
  return str_replace('\'', '', $str);
}

$user = sanitize($_GET['username']);
$password = sanitize($_GET['password']);

if(isset($_COOKIE['auth'])) {
  $loggedinUser = unserialize(base64_decode($_COOKIE['auth']));
  $_SESSION['user'] = $loggedinUser;
  header("location: /home");
  die();
}


if(strlen($user) > 20) {
  die("Username can't be more than 20 chars");
}


$sql = "SELECT * FROM users where username=\"$user\" and password = '$password'";
$result = $conn->query($sql);

if ($result->num_rows === 1) { 
  while($row = $result->fetch_assoc()) {
    if($password === $row['password']){
      $loggedinUser = new User;
      $loggedinUser->name = $row['username'];
      $loggedinUser->role = $row['role'];
      $_SESSION['user'] = $loggedinUser;
      setcookie("auth", base64_encode(serialize($loggedinUser)));
      header("location: /home");
      die();
  }
  }
} else {
  echo "<script>var error = 1 </script>";
}


function registerUser($params){
  if(UserAlreadyExists($params['username'])){
    return;
  }
  $user = new User;
  foreach ($params as $key => $value) {
    $user->$key = $value;
  }
  $user->register();
  setcookie("auth", base64_encode(serialize($user)));
  $_SESSION['user'] = $user;
  header("location: /home");
  die();
}

if(isset($_POST['user'])){
  registerUser($_POST);
}


$conn->close();

?>
<h1> LOGIN: </h1>
<form action="index.php" method="GET">
  <div id="error"></div>
<script>
var username="<?php echo htmlspecialchars($user); ?>"; 
if(error == 1){
  document.getElementById("error").innerHTML = "Username " + username + " doesn't exist or password is invalid";
}
</script>
Username: <input name="username">
Password: <input name="password">
<input type=submit>
</form>


<h1>OR REGISTER</h1>

<form action="register" method="POST" action="index.php">
Username: <input name="user">
Password: <input name="password">
<input type="submit">
</form>
