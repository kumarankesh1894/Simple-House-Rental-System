<?php
  require '../config/config.php';
  
  if(isset($_POST['login'])) {

    // Get data from FORM
    $username = $_POST['username'];
    $email = $_POST['username'];
    $password = $_POST['password'];

    try {
      $stmt = $connect->prepare('SELECT * FROM users WHERE username = :username OR email = :email');
      $stmt->execute(array(
        ':username' => $username,
        ':email' => $email
      ));
      $data = $stmt->fetch(PDO::FETCH_ASSOC);

      // Insert into the leads table to track the login attempt
      $leadStmt = $connect->prepare('INSERT INTO leads (username, email, source, status, lead_score) VALUES (:username, :email, :source, :status, :lead_score)');
      $leadStmt->execute(array(
        ':username' => $username,
        ':email' => $email,
        ':source' => 'login_attempt', // Indicating this is a login attempt
        ':status' => 'new', // Status can be 'new' initially
        ':lead_score' => 0 // Initial lead score, you can modify based on the user action
      ));

      if($data == false){
        $errMsg = "User $username not found.";
      }
      else {
        if(md5($password) == $data['password']) {
          $_SESSION['id'] = $data['id'];
          $_SESSION['username'] = $data['username'];
          $_SESSION['fullname'] = $data['fullname'];
          $_SESSION['role'] = $data['role'];
          
          // Update lead status to 'contacted' or any relevant status when user successfully logs in
          $leadStmt = $connect->prepare('UPDATE leads SET status = :status, lead_score = lead_score + :lead_score WHERE email = :email OR username = :username');
          $leadStmt->execute(array(
            ':status' => 'contacted',
            ':lead_score' => 10, // Increment the lead score by 10 or any value you want
            ':email' => $email,
            ':username' => $username
          ));

          header('Location: dashboard.php');
          exit;
        }
        else {
          $errMsg = 'Password does not match.';
        }
      }
    }
    catch(PDOException $e) {
      $errMsg = $e->getMessage();
    }
  }
?>

<?php include '../include/header.php';?>
<!-- Services -->
<nav class="navbar navbar-expand-lg navbar-dark" style="background-color:#212529;" id="mainNav">
  <div class="container">
    <a class="navbar-brand js-scroll-trigger" href="../index.php">SHRS</a>
    <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
      Menu
      <i class="fa fa-bars"></i>
    </button>
    <div class="collapse navbar-collapse" id="navbarResponsive">
      <ul class="navbar-nav text-uppercase ml-auto">
        <li class="nav-item">
          <!-- <a class="nav-link" href="login.php">Login</a> -->
        </li>
        <li class="nav-item">
          <a class="nav-link" href="register.php">Register</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<section id="services">
  <div class="container">
    <div class="row">				
      <div class="col-md-4 mx-auto">
        <div class="alert alert-info" role="alert">
          <?php
            if(isset($errMsg)){
              echo '<div style="color:#FF0000;text-align:center;font-size:17px;">'.$errMsg.'</div>';
            }
          ?>
          <h2 class="text-center">Login Panel</h2>
          <form action="" method="post">
            <div class="form-group">
              <label for="exampleInputEmail1">Email Address/Username</label>
              <input type="text" class="form-control" id="exampleInputEmail1" placeholder="Email" name="username" required>
            </div>
            <div class="form-group">
              <label for="exampleInputPassword1">Password</label>
              <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Password" name="password" required>
            </div>
            <button type="submit" class="btn btn-success" name='login' value="Login">Login</button>
          </form>				 
        </div>
      </div>
    </div>
  </div>
</section>
<?php include '../include/footer.php';?>
