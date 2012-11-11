This package contains one or more filter definition files for SSEQ-LIB security Library (aka: PHP pplication and Website Defense").

Get SSEQ-LIB from here:
http://www.erich-kachel.de/seq_lib
http://code.google.com/p/sseq-lib/downloads/list


HOWTO use filter definition files
-------------------------------------------

(You should first have SSEQ-LIB up and running on the webserver, included into the desired PHP file)

1. Copy the file(s) from this package into the "sseq-filter" directory on your web server.
2. Call SEQ_SANITIZE in your PHP file with one or more of these filter definition files you just copied.

Like this:
  SEQ_SANITIZE('sseq-filter/oscommerce_2.0.txt', true);


DONE
