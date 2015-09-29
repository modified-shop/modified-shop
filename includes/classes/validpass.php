<?php
/* -----------------------------------------------------------------------------------------
   $Id$

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2013 [www.modified-shop.org]
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2004-2006 Solar Designer <solar at openwall.com>
   (c) 2009-2011 Sam Weiss http://eschercms.org/

   See the original project's web site: http://www.openwall.com/phpass/

   changed portable hash implementation to use sha1 instead of md5, and changed
   the hash signature to "$Q$" so that the hash is not mistaken for an md5 hash.

   -----------------------------------------------------------------------------------------
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/


class validpass {
  private $itoa64;
	private $iteration_count_log2;
	private $portable_hashes;
	private $random_state;


	public function __construct($iterationCountLog2=8, $portableHashes=false)
	{
		$this->itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

		if ($iterationCountLog2 < 4 || $iterationCountLog2 > 31)
		{
			$iterationCountLog2 = 8;
		}
		
		$this->iteration_count_log2 = $iterationCountLog2;
		$this->portable_hashes = $portableHashes;
		$this->random_state = microtime() . getmyinode() . '-' . getmypid();
	}


	public function encrypt_password($plain)
	{
		$random = '';

		if (CRYPT_BLOWFISH === 1 && !$this->portable_hashes && function_exists('version_compare') && version_compare(phpversion(), '5.3.7', '>='))
		{
			$random = $this->getRandomBytes(16);
			$hash = crypt($plain, $this->gensalt_blowfish($random));
			if (strlen($hash) === 60 && $this->validate_password($plain, $hash) === true)
			{
				return $hash;
			}
		}

		if (CRYPT_EXT_DES === 1 && !$this->portable_hashes && function_exists('version_compare') && version_compare(phpversion(), '5.3.0', '>='))
		{
			if (strlen($random) != 3)
			{
				$random = $this->getRandomBytes(3);
			}
			$hash = crypt($plain, $this->gensalt_ext_des($random));
			if (strlen($hash) === 20 && $this->validate_password($plain, $hash) === true)
			{
				return $hash;
			}
		}

		if (strlen($random) != 6)
		{
			$random = $this->getRandomBytes(6);
		}
		$hash = $this->crypt_portable($plain, $this->gensalt_portable($random));
		if (strlen($hash) === 39  && $this->validate_password($plain, $hash) === true)
		{
			return $hash;
		}

		# Returning '*' on error is safe here, but would _not_ be safe
		# in a crypt(3)-like function used _both_ for generating new
		# hashes and for validating messages against existing hashes.

		return '*';
	}


	public function validate_password($plain, $encrypted)
	{
		$hash = $this->crypt_portable($plain, $encrypted);
		
		if ($hash[0] === '*')
		{
			$hash = crypt($plain, $encrypted);
		}
		
		return ($hash === $encrypted);
	}


	protected function getRandomBytes($count)
	{
		$output = '';

		if (strlen($output) < $count)
		{
			$output = '';
			for ($i = 0; $i < $count; $i += 16)
			{
				$this->random_state = sha1(microtime() . $this->random_state);
				$output .= sha1($this->random_state, true);
			}
			$output = substr($output, 0, $count);
		}

		return $output;
	}


	protected function encode64($input, $count)
	{
		$output = '';
		$i = 0;
		
		do
		{
			$value = ord($input[$i++]);
			$output .= $this->itoa64[$value & 0x3f];
			if ($i < $count)
			{
				$value |= ord($input[$i]) << 8;
			}
			$output .= $this->itoa64[($value >> 6) & 0x3f];
			if ($i++ >= $count)
			{
				break;
			}
			if ($i < $count)
			{
				$value |= ord($input[$i]) << 16;
			}
			$output .= $this->itoa64[($value >> 12) & 0x3f];
			if ($i++ >= $count)
			{
				break;
			}
			$output .= $this->itoa64[($value >> 18) & 0x3f];
		} while ($i < $count);

		return $output;
	}


	protected function crypt_portable($message, $setting)
	{
		$output = '*0';
		
		if (substr($setting, 0, 2) === $output)
		{
			$output = '*1';
		}

		if (substr($setting, 0, 3) != '$Q$')
		{
			return $output;
		}

		$count_log2 = strpos($this->itoa64, $setting[3]);
		if ($count_log2 < 7 || $count_log2 > 30)
		{
			return $output;
		}

		$count = 1 << $count_log2;

		$salt = substr($setting, 4, 8);
		if (strlen($salt) != 8)
		{
			return $output;
		}

		$hash = sha1($salt . $message, TRUE);
		do
		{
			$hash = sha1($hash . $message, TRUE);
		} while (--$count);

		$output = substr($setting, 0, 12) . $this->encode64($hash, 20);

		return $output;
	}


	protected function gensalt_portable($input)
	{
		$output = '$Q$';
		$output .= $this->itoa64[min($this->iteration_count_log2 + 5, 30)];
		$output .= $this->encode64($input, 6);

		return $output;
	}


	protected function gensalt_ext_des($input)
	{
		$count_log2 = min($this->iteration_count_log2 + 8, 24);
		# This should be odd to not reveal weak DES keys, and the
		# maximum valid value is (2**24 - 1) which is odd anyway.
		$count = (1 << $count_log2) - 1;

		$output = '_';
		$output .= $this->itoa64[$count & 0x3f];
		$output .= $this->itoa64[($count >> 6) & 0x3f];
		$output .= $this->itoa64[($count >> 12) & 0x3f];
		$output .= $this->itoa64[($count >> 18) & 0x3f];

		$output .= $this->encode64($input, 3);

		return $output;
	}


	protected function gensalt_blowfish($input)
	{
		# This one needs to use a different order of characters and a
		# different encoding scheme from the one in encode64() above.
		# We care because the last character in our encoded string will
		# only represent 2 bits.  While two known implementations of
		# bcrypt will happily accept and correct a salt string which
		# has the 4 unused bits set to non-zero, we do not want to take
		# chances and we also do not want to waste an additional byte
		# of entropy.
		$itoa64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

		$output = '$2a$';
		$output .= chr(ord('0') + $this->iteration_count_log2 / 10);
		$output .= chr(ord('0') + $this->iteration_count_log2 % 10);
		$output .= '$';

		$i = 0;
		do
		{
			$c1 = ord($input[$i++]);
			$output .= $itoa64[$c1 >> 2];
			$c1 = ($c1 & 0x03) << 4;
			if ($i >= 16)
			{
				$output .= $itoa64[$c1];
				break;
			}

			$c2 = ord($input[$i++]);
			$c1 |= $c2 >> 4;
			$output .= $itoa64[$c1];
			$c1 = ($c2 & 0x0f) << 2;

			$c2 = ord($input[$i++]);
			$c1 |= $c2 >> 6;
			$output .= $itoa64[$c1];
			$output .= $itoa64[$c2 & 0x3f];
		} while (1);

		return $output;
	}
}
?>