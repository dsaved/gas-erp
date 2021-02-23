<?php
class Msg 
{
    /**Alert messages for the system
     * static variables
     */
    public static $successpassword = 'Success : Your password has been sent to your phone.';
    public static $passwordinstruc = 'Good : an instruction on how to reset your password has been emailed to you.';
    public static $notregistered = 'Oops : This account is not registered ';
    public static $pswdchanged = 'Well done : your password has been changed successfully ';
    public static $loginerror = 'Oops : Incorrect Details Provided';
    public static $accessdenied = 'Access Denied';
    public static $logouterror = 'Oops Failed To Logout';
    public static $incorrect_password = "Current password is incorrect";
    
    public static $logoutsuccess = 'Signed Out Successfully';
    public static $failedtosendmail = 'Oops : we cannot send mails at the moment, please try again later.';
    public static $failedtosendsms = 'Oops : we cannot send sms at the moment, please try again later.';
    public static $unknownusergrp = 'Oops : You belong to an unknow user group.';
    public static $notloggedin = 'You are not yet loggedin. Please login to gain access to your account.';

    public static $provide_password = "Please provide password";
    public static $provide_current_password = "Please provide current password";
    public static $provide_email = "Please provide Email address";
    public static $provide_token = "Please provide token";
    public static $provide_username = "Please provide username";
    public static $provide_name = "Please provide name";
    public static $please_choose_access_level = "Please choose access level";
    public static $provide_code = "Please provide code";
    public static $provide_phone = "Please provide phone number";
    public static $provide_user = "Please provide user identity";
    public static $provide_access_type = "Please provide access type";
    public static $provide_otp = "Please provide the OTP sent to your phone number";
    public static $provide_bank_name = "Please provide bank name";

    public static $incorrect_password_length = "Password length is too short";
    public static $unknow_phone = "unknown phone number provided";
    public static $unknow_email = "unknown email address provided";

    public static $provide_user_id = "Please provied user id";
    public static $no_files_for_download = "No Files Available For Download";

    public static $no_users = "No users available";
    public static $no_bank = "No banks available";
    public static $no_bank_account = "No bank accounts available";
    public static $no_bank_statement = "No bank statements available";
    public static $request_failed = "request failed";
    public static $request_successful = "request successful";
    
    public static $no_auditlogs = "No auditlogs available";
    public static $no_jobsavailable = "No Available Jobs";

    public static $no_bog_unauthorized_account = "No Unauthorized Transactions";

    public static $invalid_request = "invalid request";
    public static $invalid_country = "Invalid Country";
    public static $invalid_phone_length = "Invalid phone number length";
    public static $invalid_code = "invalid code provided";
    public static $invalid_token = "invalid token provided";

    public static $phone_verified = "phone number verified";
    public static $phone_verification_failed = "failed to verify phone, please try again";
    public static $email_verified = "email address verified";
    public static $email_verification_failed = "failed to verify email address, please try again";

    public static $requiers_verification = "requies verification";

    public static $verification_sms_sent = "verification sms sent successfully";
    public static $verification_sms_not_sent = "could not send verificayion sms at the moment";

    public static $verification_email_sent = "verification mail sent successfully";
    public static $verification_email_not_sent = "could not send verificayion email at the moment";

    public static  function otp_messgae($smsCode){ return "Your one time password is: $smsCode . Please do not share with anyone";}
    public static  function phone_exist($phone){ return "the number '$phone' already exist";}
    public static  function email_exist($email){ return "the email '$email' already exist";}
    public static  function username_exist($username){ return "the username '$username' already exist";}
    public static  function company_exist($username){ return "the name '$username' already exist";}
    public static  function bank_exist($username){ return "the bank '$username' already exist";}
    public static  function account_exist($username){ return "the account number '$username' already exist";}

    public static $account_create_error = "Error while creating account";
    public static $bank_create_error = "Error while creating bank";
    public static $account_update_error = "Error while updating account";
    
    /**Email messages for the system
     * static methodes which returns constructed text.
     */
    public static function password_message_email($password){return '<p>Dear User,
    <p>Someone requested for the password of '.APP_NAME.' login. If this was a mistake, just ignore this email and nothing will happen.
    <p>We advice that you change your password frequently. See your password below: 
    <p>Password: '.$password.' . Please change your password after you sign in.
    <p>Thank you';}

    /**Email messages for the system
     * static methodes which returns constructed text.
     */
    public static function password_message_sms($password){return 'Password: '.$password.' . Please change your password after you sign in. Thank you';}

    public static function welcomenewuser($user){return "<p>Welcome ".$user['firstname']." ".$user['lastname']."<p>Thanks for creating an account with us, we appreciate you becoming a part of our community.
        <p>If you have any questions about an order or any of our products, we're always happy to help, and our customer service team is only an email away 🙂.
        <p>First, Your login details:
        <p><ul><li>Username:  ".$user['username']."</li>
        <li>Password: ".$user['password']."</li>
        <li>You can login here: <a style=\"color: #FFA73B;\" href=\"".SITE_LINK."signin/\">".SITE_LINK."signin/</a></li></ul>
        <p>Please click the botton below to activate your account.";}

    public static function verify_email($user){return "<p>Account verification ".$user['firstname']." ".$user['lastname']."</p> please verify your account";}

    public static function inst_msg(){return '<p>Dear User,
    <p>A request has been made to rest your password on '.APP_NAME.' Application. If this was a mistake, just ignore this email and no action will be taken.
    <p>We advice that you change your password frequently. Click the button bellow to reset your account password: ';}

    public static function order_placed_to_site($ordernumber, $paymethod, $date){return "An order has been made to your store. Order# $ordernumber, Payment Mode: $paymethod, Due Date: $date";}
    public static function item_removed($user, $product){return "Hello $user, An item in your cart has been removed, because the item has been deleted from our inventory. See below for item details. .  Item: $product->name.. Cost: $product->sellingprice.";}
    public static function product_shipping($user, $ordernumber,$status){return "Dear $user, The items associated with this $ordernumber  order number has been shipped..  Status: $status";}
    public static function product_shipped($user, $ordernumber,$item){return "Dear $user, Delivery proccess of your order $ordernumber  has been completed.. Item(s) expected: $item";}

    /**Alert messages for the system
     * methods variables
     * Returns message 
     */
    public static function notpermited($type){return '<span id="error">You are not allowed here.. You do not have "'.$type.'" privilege</span>';}
    public static function wecomeuser(){return 'Welcome back';}
    public static function actionerror($action ,$controller_class){return "$action Action  Was  Not Found In $controller_class";}
    public static function errorview($view,$file,$line){return "Sorry  Could not find View \"".$view."\" in \"".VIEWS."\" Caused By  ".$file." At Line  ".$line;}
    public static function nocontroller($controller){return "Error 404 No controller \"".$controller."\" found here \"".CONTROLLERS."\"";}
    public static function newaccount($phone){return "Account has been created successfully. Your username and password is: {$phone}. visit: ".SITE_LINK;}
    public static function newaccount_1($phone,$password){return "Account has been created successfully. Your username is: {$phone} and password is: {$password}. visit: ".SITE_LINK;}
}
