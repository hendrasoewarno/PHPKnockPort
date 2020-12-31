# PHPPortKnocking
Dalam rangka keamanan, sering kali kita perlu menutup beberapa port yang dapat membawa resiko kepada penyerangan, tetapi pada sisi lain kita juga membutuhkan layanan tersebut untuk melakukan kegiatan remote maintenance. Untuk membalance kedua trade-off tersebut dibutuhkan suatu fitur knock port.
### Membuat htaccess
```
htpasswd -c /etc/apache2/passwd.txt guest
mkdir /var/www/pintu
sudo pico /var/www/pintu/.htaccess
  AuthType Basic
  AuthName "Authentication Required"
  AuthUserFile "/etc/apache2/passwd.txt"
  # Here is where we allow/deny
  Order Deny,Allow
  Satisfy any
  Deny from all
  Require valid-user
```
### Membuat library.php
```
sudo pico /var/www/pintu/library.php
<?php
// Function to get the client IP address
function get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function allow($ipAddress, $portNumber) {
$f = fopen("/usr/bin/allow.sh", "a");
fwrite($f, "/sbin/iptables -A INPUT -p tcp -s $ipAddress --dport $portNumber -j$
fclose($f);
}
?>
```
### Membuat index.php
```
sudo pico /var/www/pintu/index.php
<?php
include "library.php";

echo "Welcome " . get_client_ip();
?>
```
### Membuat nomor22.php
```
sudo pico /var/www/pintu/nomor22.php
<?php
include "library.php";
try {
        allow(get_client_ip(), "22");
        echo "Connect from " . get_client_ip() . " to port 22 Allowed";
} catch (Exception $e) {
        echo "Failed!";
}
?>
```
### Membuat runallow.sh
```
sudo pico /usr/bin/runallow.sh
#!/bin/sh
echo "#!/bin/sh\n" > /usr/bin/allow.sh
```
lakukan setting ke executable
```
chmod 755 /usr/bin/runallow.sh
```
### Membuat allow.sh
```
sudo pico /usr/bin/allow.sh
#!/bin/sh
```
lakukan setting owner dan executable
```
chown www-data /usr/bin/allow.sh
```
### Membuat crontab untuk mengaktifkan runallow.sh per-5 menit
```
crontab -e
# m h  dom mon dow   command
*/5 * * * * /usr/bin/runallow.sh
```
