Official EU files for the GARAN label
=====================================

The files in this directory and in ../fonts/ are the unchanged official ones.
Replace them only with a newer official version, never with an edited copy.

1. Templates, in this directory:

   garan_label_colour.svg   from GARAN Label_colour.svg
   garan_label_nested.svg   from GARAN Label_nested display.svg

   Source: https://commission.europa.eu/publications/practical-guidelines-and-
           high-resolution-vector-files-eu-notice-and-label-product-guarantees_en

   Three fields are replaced at runtime. Each one has to stay the complete text
   of exactly one text element, otherwise nothing is rendered:

   XX                 duration of the guarantee, both templates
   Brand/Trademark    name of the manufacturer, colour template only
   Model identifier   model identifier, colour template only

   The templates split a field over several tspans to carry the kerning of the
   placeholder. The renderer therefore replaces the content of the whole text
   element instead of the token, which keeps position, class and font.

2. Fonts, in ../fonts/ :

   Inter-Regular.ttf     Inter-Regular.woff2
   Inter-ExtraBold.ttf   Inter-ExtraBold.woff2

   Only these two weights are referenced by the templates. Inter is licensed
   under the SIL Open Font License, see LICENSE.txt next to the files. The TTF
   files are used for the server side text measurement, the WOFF2 files for the
   browser output. Direct HTTP access to TTF stays blocked by images/.htaccess,
   WOFF2 is allowed.

3. Measurements, in includes/classes/guarantee_labels_renderer.php :

   areas() carries font, font size and the available width per field, read from
   the shipped templates. A new official template version may change them, so
   check the values against the file before replacing it.

The language dependent notice on the legal guarantee is not part of this
directory. It lives in the language packages as lang/<language>/notice.svg.
