<?php
header("Location: thankyou.php");
exit();

// Clean, self-contained PHP file for the thank you pop-up UI
?>

<div id="thankYouPopup" style="display: flex; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: white; padding: 40px; border-radius: 15px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.2); max-width: 400px; width: 90%;">
        
        <div style="font-size: 70px; color: #28a745; margin-bottom: 15px;">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        
        <h2 style="margin: 0 0 10px 0; font-family: Arial, sans-serif; color: #333; font-weight: bold;">Thank You!</h2>
        <p style="margin: 0 0 20px 0; font-family: Arial, sans-serif; color: #666; line-height: 1.5; font-size: 16px;">Your report has been successfully sent.</p>
        
        <a href="dashboard.php" style="display: inline-block; background: #28a745; color: white; text-decoration: none; padding: 12px 35px; font-size: 16px; border-radius: 5px; font-family: Arial, sans-serif; font-weight: bold; transition: background 0.2s;">
            Dismiss
        </a>
    </div>
</div>