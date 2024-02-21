<?php
   /***************************************************************/
   /* PhpCaptcha - A visual and audio CAPTCHA generation library
   
      Software License Agreement (BSD License)
   
      Copyright (C) 2005-2006, Edward Eliot.
      All rights reserved.
      
      Redistribution and use in source and binary forms, with or without
      modification, are permitted provided that the following conditions are met:

         * Redistributions of source code must retain the above copyright
           notice, this list of conditions and the following disclaimer.
         * Redistributions in binary form must reproduce the above copyright
           notice, this list of conditions and the following disclaimer in the
           documentation and/or other materials provided with the distribution.
         * Neither the name of Edward Eliot nor the names of its contributors 
           may be used to endorse or promote products derived from this software 
           without specific prior written permission of Edward Eliot.

      THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDER AND CONTRIBUTORS "AS IS" AND ANY
      EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
      WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
      DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY
      DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
      (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
      LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
      ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
      (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
      SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
   
      Documentation is available at http://www.ejeliot.com/pages/2

      Last Updated:  18th April 2006                               */
   /***************************************************************/
   
   /************************ Default Options **********************/
      
   define('CAPTCHA_SESSION_ID', 'vvcode');
   define('CAPTCHA_WIDTH', 200); // max 500
   define('CAPTCHA_HEIGHT', 50); // max 200
   define('CAPTCHA_NUM_CHARS', 5);
   define('CAPTCHA_NUM_LINES', 70);
   define('CAPTCHA_CHAR_SHADOW', false);
   define('CAPTCHA_OWNER_TEXT', '');
   define('CAPTCHA_CHAR_SET', ''); // defaults to A-Z
   define('CAPTCHA_CASE_INSENSITIVE', true);
   define('CAPTCHA_BACKGROUND_IMAGES', '');
   define('CAPTCHA_MIN_FONT_SIZE', 16);
   define('CAPTCHA_MAX_FONT_SIZE', 25);
   define('CAPTCHA_USE_COLOUR', false);
   define('CAPTCHA_FILE_TYPE', 'jpeg');
   define('CAPTCHA_FLITE_PATH', '/usr/bin/flite');
   define('CAPTCHA_AUDIO_PATH', '/tmp/'); // must be writeable by PHP process
   
   require_once(DIR_FS_INC.'xtc_rand.inc.php');
   
   /************************ End Default Options **********************/
   
   // don't edit below this line (unless you want to change the class!)
   
   class PhpCaptcha {
      var $oImage;
      var $aFonts;
      var $iWidth;
      var $iHeight;
      var $iNumChars;
      var $iNumLines;
      var $iSpacing;
      var $bCharShadow;
      var $sOwnerText;
      var $aCharSet;
      var $bCaseInsensitive;
      var $vBackgroundImages;
      var $iMinFontSize;
      var $iMaxFontSize;
      var $bUseColour;
      var $sFileType;
      var $captcha;
      var $sCode = '';
      
      var $aBackgroundColors = array();
      var $aLinesColors = array();
      var $aCharsColors = array();
      
      
      function __construct(
         $aFonts, // array of TrueType fonts to use - specify full path
         $iWidth = CAPTCHA_WIDTH, // width of image
         $iHeight = CAPTCHA_HEIGHT // height of image
      ) {
         // get parameters
         $this->aFonts = $aFonts;
         $this->SetNumChars(CAPTCHA_NUM_CHARS);
         $this->SetNumLines(CAPTCHA_NUM_LINES);
         $this->DisplayShadow(CAPTCHA_CHAR_SHADOW);
         $this->SetOwnerText(CAPTCHA_OWNER_TEXT);
         $this->SetCharSet(CAPTCHA_CHAR_SET);
         $this->CaseInsensitive(CAPTCHA_CASE_INSENSITIVE);
         $this->SetBackgroundImages(CAPTCHA_BACKGROUND_IMAGES);
         $this->SetMinFontSize(CAPTCHA_MIN_FONT_SIZE);
         $this->SetMaxFontSize(CAPTCHA_MAX_FONT_SIZE);
         $this->UseColour(CAPTCHA_USE_COLOUR);
         $this->SetFileType(CAPTCHA_FILE_TYPE);   
         $this->SetWidth($iWidth);
         $this->SetHeight($iHeight);
      }
      
      function SetBackgroundColors($rgb) {
        $rgb = preg_replace("'[\r\n\s]+'",'',$rgb);
        $rgb_arr = explode(',', $rgb);
        $this->aBackgroundColors['R'] = $rgb_arr[0];
        $this->aBackgroundColors['G'] = $rgb_arr[1];
        $this->aBackgroundColors['B'] = $rgb_arr[2];
      }
      
      function SetLinesColors($rgb) {
        $rgb = preg_replace("'[\r\n\s]+'",'',$rgb);
        $rgb_arr = explode(',', $rgb);
        $this->aLinesColors['R'] = $rgb_arr[0];
        $this->aLinesColors['G'] = $rgb_arr[1];
        $this->aLinesColors['B'] = $rgb_arr[2];
      }
      
      function SetCharsColors($rgb) {
        $rgb = preg_replace("'[\r\n\s]+'",'',$rgb);
        $rgb_arr = explode(',', $rgb);
        $this->aCharsColors['R'] = $rgb_arr[0];
        $this->aCharsColors['G'] = $rgb_arr[1];
        $this->aCharsColors['B'] = $rgb_arr[2];
      }
      
      function CalculateSpacing() {
         $this->iSpacing = (int)($this->iWidth / $this->iNumChars) - 0.5;
      }
      
      function SetWidth($iWidth) {
         $this->iWidth = $iWidth;
         if ($this->iWidth > 500) $this->iWidth = 500; // to prevent perfomance impact
         $this->CalculateSpacing();
      }
      
      function SetHeight($iHeight) {
         $this->iHeight = $iHeight;
         if ($this->iHeight > 200) $this->iHeight = 200; // to prevent performance impact
      }
      
      function SetNumChars($iNumChars) {
         $this->iNumChars = $iNumChars;
         $this->CalculateSpacing();
      }
      
      function SetNumLines($iNumLines) {
         $this->iNumLines = $iNumLines;
      }
      
      function DisplayShadow($bCharShadow) {
         $this->bCharShadow = $bCharShadow;
      }
      
      function SetOwnerText($sOwnerText) {
         $this->sOwnerText = $sOwnerText;
      }
      
      function SetCharSet($vCharSet) {
         // check for input type
         if (is_array($vCharSet)) {
            $this->aCharSet = $vCharSet;
         } else {
            if ($vCharSet != '') {
               // split items on commas
               $aCharSet = explode(',', $vCharSet);
            
               // initialise array
               $this->aCharSet = array();
            
               // loop through items 
               foreach ($aCharSet as $sCurrentItem) {
                  // a range should have 3 characters, otherwise is normal character
                  if (strlen($sCurrentItem) == 3) {
                     // split on range character
                     $aRange = explode('-', $sCurrentItem);
                  
                     // check for valid range
                     if (count($aRange) == 2 && $aRange[0] < $aRange[1]) {
                        // create array of characters from range
                        $aRange = range($aRange[0], $aRange[1]);
                     
                        // add to charset array
                        $this->aCharSet = array_merge($this->aCharSet, $aRange);
                     }
                  } else {
                     $this->aCharSet[] = $sCurrentItem;
                  }
               }
            }
         }
      }
      
      function CaseInsensitive($bCaseInsensitive) {
         $this->bCaseInsensitive = $bCaseInsensitive;
      }
      
      function SetBackgroundImages($vBackgroundImages) {
         $this->vBackgroundImages = $vBackgroundImages;
      }
      
      function SetMinFontSize($iMinFontSize) {
         $this->iMinFontSize = $iMinFontSize;
      }
      
      function SetMaxFontSize($iMaxFontSize) {
         $this->iMaxFontSize = $iMaxFontSize;
      }
      
      function UseColour($bUseColour) {
         $this->bUseColour = $bUseColour;
      }
      
      function SetFileType($sFileType) {
         // check for valid file type
         if (in_array($sFileType, array('gif', 'png', 'jpeg'))) {
            $this->sFileType = $sFileType;
         } else {
            $this->sFileType = 'jpeg';
         }
      }

      function iRandColour($rc1, $rc2, $rc3) {
         $iRandColour = imagecolorexact($this->oImage, $rc1, $rc2, $rc3);
         if ($iRandColour == -1) {
              //color does not exist...
              //test if we have used up palette
              if (imagecolorstotal($this->oImage) >= 255) {
                   //palette used up; pick closest assigned color
                   $iRandColour = imagecolorclosest($this->oImage, $rc1, $rc2, $rc3);
              } else {
                   //palette NOT used up; assign new color
                   $iRandColour = imagecolorallocate($this->oImage, $rc1, $rc2, $rc3);
              }
         }
         
         return $iRandColour;
      }
      
      function DrawLines() {
         $minColor = 100;
         $maxColor = 250;
         for ($i = 0; $i < $this->iNumLines; $i++) {
            // allocate colour
            if ($this->bUseColour) {
               $iLineColour = $this->iRandColour(xtc_rand($minColor, $maxColor), xtc_rand($minColor, $maxColor), xtc_rand($minColor, $maxColor));
            } else {
               if(count($this->aLinesColors)) {
                  $iLineColour = $this->iRandColour($this->aLinesColors['R'], $this->aLinesColors['G'], $this->aLinesColors['B']);
               } else {
                  $iRandColour = xtc_rand($minColor, $maxColor);
                  $iLineColour = $this->iRandColour($iRandColour, $iRandColour, $iRandColour);
               }
            }
            
            // draw line
            imageline($this->oImage, xtc_rand(0, $this->iWidth), xtc_rand(0, $this->iHeight), xtc_rand(0, $this->iWidth), xtc_rand(0, $this->iHeight), $iLineColour);
         }
      }
      
      function DrawOwnerText() {
         // allocate owner text colour
         $iBlack = $this->iRandColour(0, 0, 0);
         // get height of selected font
         $iOwnerTextHeight = imagefontheight(2);
         // calculate overall height
         $iLineHeight = $this->iHeight - $iOwnerTextHeight - 4;
         
         // draw line above text to separate from CAPTCHA
         imageline($this->oImage, 0, $iLineHeight, $this->iWidth, $iLineHeight, $iBlack);
         
         // write owner text
         imagestring($this->oImage, 2, 3, $this->iHeight - $iOwnerTextHeight - 3, $this->sOwnerText, $iBlack);
         
         // reduce available height for drawing CAPTCHA
         $this->iHeight = $this->iHeight - $iOwnerTextHeight - 5;
      }
      
      function GenerateCode() {
         // reset code
         $this->sCode = '';
         
         // loop through and generate the code letter by letter
         for ($i = 0; $i < $this->iNumChars; $i++) {
            if (count($this->aCharSet) > 0) {
               // selectmt_random character and add to code string
               $this->sCode .= $this->aCharSet[array_rand($this->aCharSet)];
            } else {
               // selectmt_random character and add to code string
               $this->sCode .= chr(xtc_rand(65, 90));
            }
         }
         
         // save code in session variable
         if ($this->bCaseInsensitive) {
            $_SESSION[CAPTCHA_SESSION_ID] = strtoupper($this->sCode);
         } else {
            $_SESSION[CAPTCHA_SESSION_ID] = $this->sCode;
         }
      }
      
      function DrawCharacters() {
         // loop through and write out selected number of characters
         $minColor = 0;
         $maxColor = 100;

         for ($i = 0; $i < strlen($this->sCode); $i++) {
            // selectmt_random font
            $sCurrentFont = $this->aFonts[array_rand($this->aFonts)];
            
            // selectmt_random colour
            if ($this->bUseColour) {
               $iTextColour = $this->iRandColour(xtc_rand($minColor, $maxColor) , xtc_rand($minColor, $maxColor), xtc_rand($minColor, $maxColor));
            
               if ($this->bCharShadow) {
                  // shadow colour
                  $iShadowColour = $this->iRandColour(xtc_rand($minColor, $maxColor), xtc_rand($minColor, $maxColor), xtc_rand($minColor, $maxColor));
               }
            } else {
               if (count($this->aCharsColors)) {
                  $iTextColour = $this->iRandColour($this->aCharsColors['R'], $this->aCharsColors['G'], $this->aCharsColors['B']);
               } else {
                  $iRandColour = xtc_rand($minColor, $maxColor);
                  $iTextColour = $this->iRandColour($iRandColour, $iRandColour, $iRandColour);
               }
            
               if ($this->bCharShadow) {
                  // shadow colour
                  $iRandColour = xtc_rand($minColor, $maxColor);
                  $iShadowColour = $this->iRandColour($iRandColour, $iRandColour, $iRandColour);
               }
            }
            
            // selectmt_random font size
            $iFontSize = xtc_rand($this->iMinFontSize, $this->iMaxFontSize);
            
            // selectmt_random angle
            $iAngle = xtc_rand(-30, 30);
            
            // get dimensions of character in selected font and text size
            $aCharDetails = $this->ImageDimension($iFontSize, $iAngle, $sCurrentFont, $this->sCode[$i]);
            
            // calculate character starting coordinates
            $iX = $this->iSpacing / 4 + $i * $this->iSpacing;
            $iCharHeight = $aCharDetails[2] - $aCharDetails[5];
            $iY = $this->iHeight / 2 + $iCharHeight / 4; 
            
            // write text to image
            $this->WriteToImage($iFontSize, $iAngle, (int)$iX, (int)$iY, (int)$iTextColour, $sCurrentFont, $this->sCode[$i]);
            
            if ($this->bCharShadow) {
               $iOffsetAngle = xtc_rand(-30, 30);
               $iRandOffsetX = xtc_rand(-5, 5);
               $iRandOffsetY = xtc_rand(-5, 5);
               
               $this->WriteToImage($iFontSize, $iOffsetAngle, (int)($iX + $iRandOffsetX), (int)($iY + $iRandOffsetY), (int)$iShadowColour, $sCurrentFont, $this->sCode[$i]);
            }
         }
      }

      function ImageDimension($iFontSize, $iAngle, $sCurrentFont, $sCode) {
          $iDetails = array(0,0,$this->iSpacing,0,0,$this->iSpacing,0,0);
          if (function_exists('imagettfbbox')) {
            $iDetails = imagettfbbox($iFontSize, $iAngle, $sCurrentFont, $sCode);
          } elseif (function_exists('imageftbbox')) {
            $iDetails = imageftbbox($iFontSize, $iAngle, $sCurrentFont, $sCode);
          }     
          return $iDetails;
      }
      
      function WriteToImage($iFontSize, $iOffsetAngle, $iRandOffsetX, $iRandOffsetY, $iTextColour, $sCurrentFont, $sCode) {
          if (function_exists('imagettftext')) {
            imagettftext($this->oImage, $iFontSize, $iOffsetAngle, $iRandOffsetX, $iRandOffsetY, $iTextColour, $sCurrentFont, $sCode);
          } elseif (function_exists('imagefttext')) {
            imagefttext($this->oImage, $iFontSize, $iOffsetAngle, $iRandOffsetX, $iRandOffsetY, $iTextColour, $sCurrentFont, $sCode);
          } else {
            $iFontSize = 5;
            ImageString($this->oImage, $iFontSize, $iRandOffsetX, ($this->iHeight/2)-$iFontSize-xtc_rand($iFontSize*(-1), $iFontSize), $sCode, $iTextColour);  
          }
      }
      
      function WriteFile($sFilename) {
         if ($sFilename == '') {
            ob_start();
         }
         
         switch ($this->sFileType) {
            case 'gif':
               $sFilename != '' ? imagegif($this->oImage, $sFilename) : imagegif($this->oImage);
               break;
            case 'png':
               $sFilename != '' ? imagepng($this->oImage, $sFilename) : imagepng($this->oImage);
               break;
            default:
               $sFilename != '' ? imagejpeg($this->oImage, $sFilename) : imagejpeg($this->oImage);
         }

         if ($sFilename == '') {
            $this->captcha = ob_get_clean();
         }
      }
      
      function Create($sFilename = '') {
         // check for required gd functions
         if (!function_exists('imagecreate') || !function_exists("image$this->sFileType") || ($this->vBackgroundImages != '' && !function_exists('imagecreatetruecolor'))) {
            return false;
         }
         
         // get background image if specified and copy to CAPTCHA
         if (is_array($this->vBackgroundImages) || $this->vBackgroundImages != '') {
            // create new image
            $this->oImage = imagecreatetruecolor($this->iWidth, $this->iHeight);
            
            // create background image
            if (is_array($this->vBackgroundImages)) {
               $iRandImage = array_rand($this->vBackgroundImages);
               $oBackgroundImage = imagecreatefromjpeg($this->vBackgroundImages[$iRandImage]);
            } else {
               $oBackgroundImage = imagecreatefromjpeg($this->vBackgroundImages);
            }
            
            // copy background image
            imagecopy($this->oImage, $oBackgroundImage, 0, 0, 0, 0, $this->iWidth, $this->iHeight);
            
            // free memory used to create background image
            imagedestroy($oBackgroundImage);
         } else {
            // create new image
            $this->oImage = imagecreate($this->iWidth, $this->iHeight);
         }
         
         // allocate white background colour
         if (count($this->aBackgroundColors)) {
            $this->iRandColour($this->aBackgroundColors['R'], $this->aBackgroundColors['G'], $this->aBackgroundColors['B']);
         } else {
            $this->iRandColour(255, 255, 255);
         }
         
         // check for owner text
         if ($this->sOwnerText != '') {
            $this->DrawOwnerText();
         }
         
         // check for background image before drawing lines
         if (!is_array($this->vBackgroundImages) && $this->vBackgroundImages == '') {
            $this->DrawLines();
         }
         
         $this->GenerateCode();
         $this->DrawCharacters();
         
         // write out image to file or browser
         $this->WriteFile($sFilename);
         
         // free memory used in creating image
         imagedestroy($this->oImage);
         
         return $this->captcha;
      }
      
      // call this method statically
      function Validate($sUserCode, $bCaseInsensitive = true) {
         if ($bCaseInsensitive) {
            $sUserCode = strtoupper($sUserCode);
         }
         
         if (!empty($_SESSION[CAPTCHA_SESSION_ID]) && $sUserCode == $_SESSION[CAPTCHA_SESSION_ID]) {
            // clear to prevent re-use
            unset($_SESSION[CAPTCHA_SESSION_ID]);
            
            return true;
         }
         
         return false;
      }
   }
   
   // this class will only work correctly if a visual CAPTCHA has been created first using PhpCaptcha
   class AudioPhpCaptcha {
      var $sFlitePath;
      var $sAudioPath;
      var $sCode;
      
      function __construct(
         $sFlitePath = CAPTCHA_FLITE_PATH, // path to flite binary
         $sAudioPath = CAPTCHA_AUDIO_PATH // the location to temporarily store the generated audio CAPTCHA
      ) {
         $this->SetFlitePath($sFlitePath);
         $this->SetAudioPath($sAudioPath);
         
         // retrieve code if already set by previous instance of visual PhpCaptcha
         if (isset($_SESSION[CAPTCHA_SESSION_ID])) {
            $this->sCode = $_SESSION[CAPTCHA_SESSION_ID];
         }
      }
      
      function SetFlitePath($sFlitePath) {
         $this->sFlitePath = $sFlitePath;
      }
      
      function SetAudioPath($sAudioPath) {
         $this->sAudioPath = $sAudioPath;
      }
      
      function Mask($sText) {
         $iLength = strlen($sText);
         
         // loop through characters in code and format
         $sFormattedText = '';
         for ($i = 0; $i < $iLength; $i++) {
            // comma separate all but first and last characters
            if ($i > 0 && $i < $iLength - 1) {
               $sFormattedText .= ', ';
            } elseif ($i == $iLength - 1) { // precede last character with "and"
               $sFormattedText .= ' and ';
            }
            $sFormattedText .= $sText[$i];
         }
         
         $aPhrases = array(
            "The %1\$s characters are as follows: %2\$s",
            "%2\$s, are the %1\$s letters",
            "Here are the %1\$s characters: %2\$s",
            "%1\$s characters are: %2\$s",
            "%1\$s letters: %2\$s"
         );
         
         $iPhrase = array_rand($aPhrases);
         
         return sprintf($aPhrases[$iPhrase], $iLength, $sFormattedText);
      }
      
      function Create() {
         $sText = $this->Mask($this->sCode);
         $sFile = md5($this->sCode.time());
         
         // create file with flite
         shell_exec("$this->sFlitePath -t \"$sText\" -o $this->sAudioPath$sFile.wav");
         
         // set headers
         header('Content-type: audio/x-wav');
         header("Content-Disposition: attachment;filename=$sFile.wav");
         
         // output to browser
         echo file_get_contents("$this->sAudioPath$sFile.wav");
         
         // delete temporary file
         @unlink("$this->sAudioPath$sFile.wav");
      }
   }
   
   // example sub class
   class PhpCaptchaColour extends PhpCaptcha {
      function __construct($aFonts, $iWidth = CAPTCHA_WIDTH, $iHeight = CAPTCHA_HEIGHT) {
         // call parent constructor
         parent::__construct($aFonts, $iWidth, $iHeight);
         
         // set options
         $this->UseColour(true);
      }
   }
