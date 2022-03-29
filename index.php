<html>
    <head>
    <link rel="stylesheet" href="src/style.css">
    </head>
<body>

<div class="content">
<h1>Certificate Web Request</h1>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
<p><table border=0 class="tg">
    <tbody>
  		<tr>
    		<th class="tg-0lax">CN</th>
    		<th class="tg-0lax"><input type="text" name="CN"></th>
  		</tr>
        <tr>
    		<th class="tg-0lax">Password</th>
    		<th class="tg-0lax"><input type="text" name="password"></th>
  		</tr>
  		<tr>
    		<th class="tg-0lax"><input type="submit" value="Send Request"></th>
    		<th class="tg-0lax"><input type="checkbox" name="p12">Convert to .p12?<br></th>
  		</tr>
    </tbody>
    </table></p>

    </form>

<p><table border=0>
    <thead>
        <tr>
            <th colspan="2">Certificate files</th>
        <tr>
    </thead>
    <tbody>
        <tr>
    
        
<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
<?php

// Looking for files in files/
$path    = 'files/';
$files = scandir($path);
$files = array_diff(scandir($path), array('.', '..'));
foreach($files as $file){
    echo "<tr>";
    echo "<td><a href=files/$file>$file</a><br></th></td>";
    ?>
    <th><input type="checkbox" name="delid" value="<?php echo $file; ?>"></th>
    <?php
    echo "</tr>";
}
// Check if delid is set
if(isset($_POST['delid'])){
$delfile = $_POST['delid'];
    if(!empty($_POST['delid'])) {

        // Remove -public.pem & -private.pem from filename return with CN
        $regexp = '/-[a-zA-Z]+\.[a-zA-Z]+/';
        $trimmedfile = preg_replace($regexp, '', $delfile);
        // Remove file from disk
 		exec('sudo rm -f /var/www/cert/html/files/'.$delfile.'');
        // Stop tracking certificate profile
        exec('sudo getcert stop-tracking -i '.$trimmedfile.'');
        header("Location: index.php");
    } 
}
?>

<td></td>
<?php 
// Show delete button if there is a file in files/
if(!empty($file)) {
echo "<td><input type='submit' value='delete'></td>";
}
?>
<tr>
</tbody>
</table></p>
</form>
          </div>




<?php
// Check if CN is set
if (isset($_POST['CN']) ){
$CN = $_POST['CN'];
        // If CN=empty return echo
        if (empty($CN)) {
                echo "CN is empty";
        } else {
        // Check if p12 is checked
        if(!empty($_POST['p12'])) {
            // Check if password is set when p12 is checked
            if(!empty($_POST['password'])) {
                    $pw = $_POST['password'];	
                    // Send CSR for sign to CA
                    // Create profile with $CN in Derp-CA 
                    exec('sudo getcert request -c Derp-CA -I '.$CN.' -f /var/www/cert/html/files/'.$CN.'-public.pem -k /var/www/cert/html/files/'.$CN.'-private.pem -N '.$CN.' -D '.$CN.' -w');
                    sleep(2);

                    // Convert .pem files to .p12
                    exec('sudo openssl pkcs12 -export -out /var/www/cert/html/files/'.$CN.'.p12 -in /var/www/cert/html/files/'.$CN.'-public.pem -inkey /var/www/cert/html/files/'.$CN.'-private.pem -passout pass:'.$pw.'');
                    sleep(2);
                    // Change ownership of created files
                    exec('sudo chown nginx:nginx /var/www/cert/html/files/'.$CN.'-public.pem');
                    exec('sudo chown nginx:nginx /var/www/cert/html/files/'.$CN.'-private.pem');   
                    exec('sudo chown nginx:nginx /var/www/cert/html/files/'.$CN.'.p12');
                    // Change permission on created files
                    exec('sudo chmod 666 /var/www/cert/html/files/'.$CN.'.p12');
                    exec('sudo chmod 666 /var/www/cert/html/files/'.$CN.'-private.pem');
                    exec('sudo chmod 666 /var/www/cert/html/files/'.$CN.'-public.pem');
                    header("Location: index.php");
            } else {
                echo "Password cannot be empty if p12 is checked";

            }
        } else {
                    // Send CSR for sign to CA
                	exec('sudo getcert request -c Derp-CA -I '.$CN.' -f /var/www/cert/html/files/'.$CN.'-public.pem -k /var/www/cert/html/files/'.$CN.'-private.pem -N '.$CN.' -D '.$CN.' -w');
                	sleep(2);
                    // Change ownership of created files
                	exec('sudo chown nginx:nginx /var/www/cert/html/files/'.$CN.'-public.pem');
                	exec('sudo chown nginx:nginx /var/www/cert/html/files/'.$CN.'-private.pem');
                    exec('sudo chmod 666 /var/www/cert/html/files/'.$CN.'-private.pem');
                    exec('sudo chmod 666 /var/www/cert/html/files/'.$CN.'-public.pem');
                    header("Location: index.php");
        }
    }

}
?>


</body>
</html>