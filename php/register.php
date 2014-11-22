<?php 
    require_once("stripe-php/lib/Stripe.php");
    // Set your secret key: remember to change this to your live secret key in production
    // See your keys here https://dashboard.stripe.com/account
    Stripe::setApiKey("REDACTED");

    // Get the credit card details submitted by the form
    $token = $_POST['stripeToken'];

    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $package = $_POST['package'];
    $coupon = $_POST['coupon'];
    $coupon = strtoupper($coupon);
    $plan = 0.0;

    if ($package == 'FULL'):
        $plan = 300.0;
    elseif ($package == 'LEGE'):
        $plan = 200.0;
    endif;

    if ($coupon == 'EXEC' && $package == 'FULL'):
        $discount = 290.0;
    elseif ($coupon == 'EXEC' && $package == 'LEGE'):
        $discount = 190.0;
    elseif ($coupon == 'PASTPRES' && $package == 'FULL'):
        $discount = 150.0;
    elseif ($coupon == 'PASTPRES' && $package == 'LEGE'):
        $discount = 100.0;
    elseif ($coupon == 'YAT' && $package == 'FULL'):
        $discount = 105.0;
    elseif ($coupon == 'YAT' && $package == 'LEGE'):
        $discount = 50.0;
    elseif ($coupon == 'SUPEREARLY' && $package == 'FULL'):
        $discount = 155.0;
    elseif ($coupon == 'SUPEREARLY' && $package == 'LEGE'):
        $discount = 85.0;
    elseif ($coupon == 'EARLYBIRD' && $package == 'FULL'):
        $discount = 105.0;
    elseif ($coupon == 'EARLYBIRD' && $package == 'LEGE'):
        $discount = 50.0;
    elseif ($package == 'LEGE'):
        $coupon == 'SUPER EARLY';
        $discount = 85.0;
    else:
        $discount = 155.0;
        $coupon = 'SUPER EARLY';
    endif;


    // Calculate amount given any coupon codes, in cents
    // Aiming for $245 all-inclusive, $150 legislative lunch
    // 2 week "fire sale" at $195; YAT's at $195
    $charged = $plan-$discount;
    $amt = $charged*100;

    $desc = "$name";

    $meta = array(
        "Name" => $name,
        "Email" => $email,
        "Phone" => $phone,
        "Package" => $package,
        "Promo" => $coupon,
        "Paid" => $charged,
        );

    // Create the charge on Stripe's servers - this will charge the user's card
    try {
    $charge = Stripe_Charge::create(array(
      "amount" => $amt, // amount in cents, again
      "currency" => "usd",
      "card" => $token,
      "metadata" => $meta,
      "description" => $desc)
    );
    } catch(Stripe_CardError $e) {
      // The card has been declined
    };



    $email_bcc = 'amy.evans@naifa-texas.org';    
    $subject = 'Conference Registration';
    $email_from = 'NAIFA-Texas <office@naifa-texas.org>';
    $body = "<center>
            <img src='http://www.naifa-texas.org/legislativeday/assets/img/naifa-texas-logo.jpg'><br>
            <h1>You're Registered!</h1>
            <p>Thank you for registering for the NAIFA-Texas Legislative Day and Annual Conference.</p>
            </center>
            <h4><strong>ATTENDEE INFORMATION</strong></h4>			
            Name: <strong>$name</strong><br>   
            Email: <strong>$email</strong><br>          
            Phone: <strong>$phone</strong><br>
            <h4><strong>PAYMENT INFORMATION</strong></h4>
            Package: <strong>$package</strong><br>
            Value: <strong>$$plan.00</strong><br>
            Discount: <strong>$$discount.00</strong> (Promo code: $coupon)<br>
            Amount charged to card: <strong>$$charged.00</strong><br>
            <h4><strong>EVENT DETAILS</strong></h4>
            <p>If you registered for the FULL CONFERENCE (<strong>FULL</strong>), check-in will begin at noon
            on Monday, January 26 with programming beginning at 1 pm. If you registered for LEGISLATIVE 
            DAY ONLY (<strong>LEGE</strong>), you may check-in anytime Monday or Tuesday morning beginning at 
            6:30 am, with programming beginning at 7 am Tuesday, January 27.</p>
            Location: <strong>AT&T Conference Center</strong>, 1900 University Ave, Austin, TX 78705<br>
            For full event details: <strong>
            <a href='http://www.naifa-texas.org/legislativeday'>naifa-texas.org/legislativeday</a></strong><br>
            <h4>QUESTIONS</h4>
            Reply to this email or call 512-716-8800 with any questions.
            ";

    $headers = array(); 
	$headers[] = "MIME-Version: 1.0"; 
	$headers[] = "Content-type: text/html; charset=utf-8"; 
	$headers[] = "Subject: {$subject}";
    $headers[] = "From: {$email_from}"; 
    $headers[] = "Bcc: {$email_bcc}";
    $headers[] = "X-Mailer: PHP/".phpversion();
    

       
    $ok = mail($email, $subject, $body, implode("\r\n", $headers));
    if($ok)
        header("Location: http://www.naifa-texas.org/legislativeday/registered.html");
        // echo '1';
    else
        echo '0';
?>