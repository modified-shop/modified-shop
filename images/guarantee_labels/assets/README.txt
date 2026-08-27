Official EU files for the GARAN label
=====================================

The module ships without the official artwork. Copy the unchanged EU files into
this shop before the module can render a label.

1. Templates, into this directory:

   GARAN Label_colour.svg          ->  garan_label_colour.svg
   GARAN Label_nested display.svg  ->  garan_label_nested.svg

   Source: https://commission.europa.eu/publications/practical-guidelines-and-
           high-resolution-vector-files-eu-notice-and-label-product-guarantees_en

   The files must stay unchanged. Only the three editable fields are replaced at
   runtime and each of them has to appear exactly once as the complete text of an
   element, otherwise nothing is rendered:

   XX                 duration of the guarantee
   Brand/Trademark    name of the manufacturer
   Model identifier   model identifier of the manufacturer

2. Fonts, into ../fonts/ :

   Inter-Regular.ttf     Inter-Regular.woff2
   Inter-SemiBold.ttf    Inter-SemiBold.woff2
   Inter-ExtraBold.ttf   Inter-ExtraBold.woff2

   Inter is licensed under the SIL Open Font License and may be shipped with the
   shop. The TTF files are used for the server side text measurement, the WOFF2
   files for the browser output. Direct HTTP access to TTF stays blocked by
   images/.htaccess, WOFF2 is allowed.

3. Measurements, in includes/classes/guarantee_labels_renderer.php :

   areas() carries font_size and max_width per editable field. Both are zero
   while this list is unfinished, which keeps the module from rendering a wrong
   label. Take the values from the official templates once: font_size is the size
   imagettfbbox() is called with, max_width is the width of the editable area
   measured in the same unit.

The language dependent notice on the legal guarantee is not part of this
directory. It belongs into the language packages as lang/<language>/notice.svg.
