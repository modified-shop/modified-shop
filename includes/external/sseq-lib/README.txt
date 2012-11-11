SSEQ-LIB : Simple Security library

This software is open-source. License: GNUv3
Autor: Erich Kachel; info@erich-kachel.de
http://www.erich-kachel.de/seq_lib


INSTALLATION:
----------------
Copy the whole directory "sseq-lib" into the document root of the web server.
Open the file "seq_lib.php" and set the parameter "$_SEQ_BASEDIR" to
the current directory.

Example:

if the server root is:
/srv/www/vhosts/test.de/httpdocs

copy the whole directory "sseq-lib" to:
/srv/www/vhosts/test.de/httpdocs/sseq-lib

set "$_SEQ_BASEDIR" to:
/srv/www/vhosts/test.de/httpdocs/sseq-lib/

Do not forget to close with a slash! (/)


USAGE:
----------------
Modify a PHP script to include the file "seq_lib.php":

include_once('sseq-lib/seq_lib.php');


UPDATES:
----------------
Check for security updates at:
http://www.erich-kachel.de/seq_lib