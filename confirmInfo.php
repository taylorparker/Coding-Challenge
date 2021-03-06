<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="stylesheet" href="css/styles.css"/>
  <title>Confirm Info</title>
</head>
<body>
  
<?php
if(isset($_POST['email'])) {

  function died($error) {
    echo "We are very sorry, but there were error(s) found with the form you submitted. ";
    echo "These errors appear below.<br /><br />";
    echo $error."<br /><br />";
    echo "Please go back and fix these errors.<br /><br />";
    die();
  }

  // validate data isn't null/empty
  if(!isset($_POST['firstName']) ||
      !isset($_POST['lastName']) ||
      !isset($_POST['email'])) {
      died('We are sorry, but there appears to be a problem with the form you submitted.');
  }

  $first_name = $_POST['firstName']; // required
  $last_name = $_POST['lastName']; // required
  $email = $_POST['email']; // required

  $error_message = "";

  $name_exp = "/^[A-Za-z .'-]+$/";

  if(!preg_match($name_exp, $first_name) || !preg_match($name_exp, $last_name) || strlen(trim($first_name)) == 0 || strlen(trim($last_name)) == 0) { // check if the first or last name doesn't match the regex statement, or is an empty string
    $error_message .= 'The First or Last Name you entered does not appear to be valid.<br />';
  }

  $email_exp = '/^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/';

  if(!preg_match($email_exp, $email) || strlen(trim($email)) == 0) { // check if the email doesn't match the regex statement, or is an empty string
    $error_message .= 'The Email Address you entered does not appear to be valid.<br />';
  }

  if(strlen($error_message) > 0) {
    died($error_message); // oof, we can't proceed with an error message
  }


  function clean_string($string) { // clean up the inputs for any extra text before display
    $extraText = array("content-type","bcc:","to:","cc:","href");
    return str_replace($extraText,"",$string);
  }

  /*
   * Write the contact info to the database
   * This location was chosen since it is after the user's input have been validated
   * We will still use prepared statements
  */
  try {
    include 'includes/dbCon.php';
    $conn = new mysqli($server, $user, $pass, $db);

    $stmt = $conn->prepare("INSERT INTO MockupDatabase (firstName, lastName, email) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $first_name, $last_name, $email);
    $stmt->execute();

    $stmt->close();
    $conn->close();
  } catch (Exception $e) {
    /*
    * Due to this being a mock database, it will never connect.
    * The try/catch block allows us to continue with our application instead of crashing
    */
  }
  // all database opertations are closed

  $email_to = $email;
  $email_subject = "Please confirm your contact information.";

  $email_message = "
      <html>
          <head>
          <title>Confirm Contact Info</title>
          </head>
      <body>
      <section style='background-color: #74b9ff; height: 100%; margin: auto; text-align: center; width: 100%'>
          <div>
              <p style='color: white; font-family: Arial, Helvetica, sans-serif; font-size: 20px; padding: 15px;'>Please confirm the contact information below:</p>
              <p style='color: white; font-family: Arial, Helvetica, sans-serif; font-size: 20px; padding: 15px;'>Name: ".clean_string($first_name)." ".clean_string($last_name)."</p>
              <p style='color: white; font-family: Arial, Helvetica, sans-serif; font-size: 20px; padding: 15px;'>Email: ".clean_string($email)."</p>
          </div>
      </section>
      </body>
      </html>";

  // set email headers
  $headers = "MIME-Version: 1.0" . "\r\n";
  $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
  $headers .= 'From: taylorparker2013@gmail.com' . "\r\n";
  mail($email_to, $email_subject, $email_message, $headers);
?>

<div class="send">
  <h1>Thank you entering your contact information. <br>
      A confirmation email has been sent to the email provided.</h1>

  <div class="send-button-holder">
    <a href="index.html">
      <div class="send-button">Return to Contact Form</div>
    </a>
  </div>
</div>

<?php

}
?>

</body>
</html>