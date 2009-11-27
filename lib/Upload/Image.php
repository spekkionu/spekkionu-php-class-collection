<?php
/**
 * Image Upload and resize class
 *
 * @author Jonathan Bernardi
 * @copyright 2008 Jonathan Bernardi<spekkionu@spekkionu.com>
 * @version 1.0 06/24/2008
 * @package Upload
 * @license MIT License
 */

/**
 * Upload_Image Class
 *
 */
class Upload_Image {
	
	const OVERWRITE = 'overwrite';
	const UNIQUE = 'unique';
	const ERROR = 'error';
	//const OVERWRITE = 'overwrite';
	
	private $_file;
	private $_output = array();
	private $_imagetype;
	private $_oldsize = array(
		"width"  => 0,
		"height" => 0
	);
	private $_maxsize;
	private $_newsize = array();
	private $_newfilename = array();
	private $_resize = true;
	private $_keepaspect = true;
	private $_overmethod = "overwrite";
	private $_complete = array();
	private $_tmpimg;
	
	/**
	 * Class constructor.
	 * @param string $file The file to be uploaded.  Can be a relative or absolute path.
	 */
	public function __construct($file){
		$this->_checkExtensions();
		if(!is_file($file) or !is_readable($file)){
			throw new Exception("File does not exist or is not readable.");
		}
		$this->_file = $file;
		$this->_maxsize = intval(ini_get('upload_max_filesize'))*1000000;
		$this->_imagetype = $this->_checkFileType();
		$size = getimagesize($file);
		
		$this->_oldsize = array(
			"width"  => $size[0],
			"height" => $size[1]
		);
		
	}
	
	/**
	 * Checks that required functions exist
	 * @return void
	 */
	private function _checkExtensions(){
	  if(!function_exists('getimagesize')) throw new Exception('Function getimagesize does not exist.');
	  if(!function_exists('pathinfo')) throw new Exception('Function pathinfo does not exist.');
	  if(!function_exists('imagedestroy')) throw new Exception('Function imagedestroy does not exist.');
	  if(!function_exists('imagecreatefromgif')) throw new Exception('Function imagecreatefromgif does not exist.');
	  if(!function_exists('imagecreatefromjpeg')) throw new Exception('Function imagecreatefromjpeg does not exist.');
	  if(!function_exists('imagecreatefromwbmp')) throw new Exception('Function imagecreatefromwbmp does not exist.');
	  if(!function_exists('imagecreatefrompng')) throw new Exception('Function imagecreatefrompng does not exist.');
	  if(!function_exists('imagecreatetruecolor')) throw new Exception('Function imagecreatetruecolor does not exist.');
	  if(!function_exists('imagegif')) throw new Exception('Function imagegif does not exist.');
	  if(!function_exists('imagejpeg')) throw new Exception('Function imagejpeg does not exist.');
	  if(!function_exists('image2wbmp')) throw new Exception('Function image2wbmp does not exist.');
	  if(!function_exists('imagepng')) throw new Exception('Function imagepng does not exist.');
	  if(!function_exists('image_type_to_mime_type')) throw new Exception('Function image_type_to_mime_type does not exist.');
	}
	
	/**
	 * Adds an output file.
	 * @param string $path The path to the directory to upload the file.
	 * @param string $filename The desired filename for the output file.  If empty the original filename will be used.
	 * @param integer $maxwidth The largest width allowed.  If zero will not be checked.
	 * @param integer $maxheight The largest height allowed.  If zero will not be checked.
	 * @param integer $minwidth The smallest width allowed.  If zero will not be checked.
	 * @param integer $minheight The smallest height allowed.  If zero will not be checked.
	 * @return Upload_Image
	 */
	public function addOutput($path = "./", $filename = "", $maxwidth = 0, $maxheight = 0, $minwidth = 0, $minheight = 0){
		if(!is_dir($path)){
			throw new Exception("$path is not an existing directory.");
		}
		if(!is_writable($path)){
			throw new Exception("$path is not writable. Check permissions.");
		}
		$filename = trim($filename);
		if($filename == ""){
			$fname = basename($this->_file);
		}
		$filename = str_replace(" ","_",$filename);
		$maxwidth = intval($maxwidth);
		$maxheight = intval($maxheight);
		$minwidth = intval($minwidth);
		$minheight = intval($minheight);
		if($maxwidth > 0 and ($minwidth > $maxwidth)){
			throw new Exception("Min width cannot be greater than max width");
		}
		if($maxheight > 0 and ($minheight > $maxheight)){
			throw new Exception("Min height cannot be greater than max height");
		}
		$this->_output[] = array(
			"path"      => $path,
			"filename"  => $filename,
			"maxwidth"  => $maxwidth,
			"maxheight" => $maxheight,
			"minwidth"  => $minwidth,
			"minheight" => $minheight
		);
		return $this;
	}
	
	/**
	 * Sets if the image will be resized.
	 *
	 * @param boolean $doresize If true image will be resized.  Otherwise exceptions will be thrown if image does not fit max and min dimensions.
	 * If not called will be true.
	 * @param boolean $keepaspect If true aspect ration will be kept when resizing.  If this isn't possible an exception will be thrown.  If doresize is false this isn't used.
	 * @return Upload_Image
	 */
	public function setResizeOption($doresize = true, $keepaspect = true){
		$this->_resize = ($doresize)?true:false;
		$this->_keepaspect = ($keepaspect)?true:false;
		return $this;
	}
	
	/**
	 * Sets what to do if file already exists.
	 *
	 * @param string $method can be overwrite, unique, or error.
	 * If overwrite existing file will be overwritten with new file.
	 * If unique _n will be appended to the filename until the filename is unique.
	 * If error an exception will be thrown.
	 * If not called overwrite will be used.
	 * @return Upload_Image
	 */
	public function overwriteMethod($method = self::OVERWRITE){
		$method = strtolower(trim($method));
		switch($method){
			case self::OVERWRITE:
				$this->_overmethod = self::OVERWRITE;
				break;
			case self::UNIQUE:
				$this->_overmethod = self::UNIQUE;
				break;
			case self::ERROR:
				$this->_overmethod = self::ERROR;
				break;
			default:
				throw new Exception("Incorrect parameter for overwriteMethod(). Must be overwrite, unique, or error.");
				break;
		}
		return $this;
	}
	
	/**
	 * Sets the maximum filesize of the uploaded file.
	 * If not called upload_max_filesize ini setting is used.
	 * Set a lower number if memory keeps running out.
	 *
	 * @param integer $size The max filesize in bytes.
	 * @return Upload_Image
	 */
	public function setMaxFileSize($size){
		$size = intval($size);
		$this->_maxsize = min($size,1000000 * intval(ini_get('upload_max_filesize')));
		return $this;
	}
	
	/**
	 * Performs the file upload and processing.
	 *
	 * @return array Returns a miltidimensional array with an index for each output file with width, height, and filename.
	 */
	public function process(){
		if(count($this->_output) == 0){
			throw new Exception("Now output files set.  Call addOutput() first");
		}
		if(filesize($this->_file) > $this->_maxsize){
			throw new Exception("File is too large.");
		}
		foreach($this->_output as $key=>$output){
			$this->_complete[$key]['filename'] = $this->_getNewFilename($key);
			$size = $this->_calculateSize($key);
			$this->_complete[$key]['width'] = $size['width'];
			$this->_complete[$key]['height'] = $size['height'];
			$this->_saveImage($key);
		}
		@imagedestroy($this->_tmpimg);
		return $this->_complete;
	}
	
	/**
	 * Generates the new filename
	 * @param string $key
	 * @return string
	 */
	private function _getNewFilename($key){
		$file = $this->_output[$key];
		$oldfilename = $file['filename'];
		$info = pathinfo($oldfilename);
		// Check if filename already exists.
		if(!file_exists($file['path'] . "/" . $oldfilename)){
			$this->_newfilename[$key] = $oldfilename;
			return $oldfilename;
		}
		switch($this->_overmethod){
			case "overwrite":
				$this->_newfilename[$key] = $oldfilename;
				return $oldfilename;
				break;
			case "unique":
				//Create a unique filename.
				$j = 1;
				do{
					$fn = $info['filename'] . "_" . $j;
					$j++;
					$name = $fn . "." . $info['extension'];
				}while(file_exists($file['path'] . "/" . $name));
				$this->_newfilename[$key] = $name;
				return $name;
				break;
			case "error":
				throw new Exception("File already exists and duplicate method set to error.");
				break;
			default:
				throw new Exception("Incorrect parameter for overwriteMethod(). Must be overwrite, unique, or error.");
				break;
		}
	}
	
	/**
	 * Save the image
	 * @param string $key
	 * @return void
	 */
	private function _saveImage($key){
		$output = $this->_output[$key];
		$type = $this->_imagetype;
		$size = $this->_newsize[$key];
		switch($type){
			case "gif":
				$im = imagecreatefromgif($this->_file);
				break;
			case "jpg":
				$im = imagecreatefromjpeg($this->_file);
				break;
			case "bmp":
				$im = imagecreatefromwbmp($this->_file);
				break;
			case "png":
				$im = imagecreatefrompng($this->_file);
				break;
			default:
				throw new Exception("Incorrect image type.");
				break;
		}
		if(!$im){
			throw new Exception("Could not open image file.");
		}
		$newimage = imagecreatetruecolor($size['width'],$size['height']);
		if(!$newimage){
			throw new Exception("Unable to create new image.");
		}
		$white = imagecolorallocate($newimage, 255, 255, 255);
		imagefill($newimage, 0, 0, $white);
		if(!imagecopyresampled($newimage,$im,0,0,0,0,$size['width'],$size['height'],$this->_oldsize['width'],$this->_oldsize['height'])){
			throw new Exception("Unable to copy image.");
		}
		imagedestroy($im);
		$filename = $output['path'] . "/" . $this->_newfilename[$key];
		switch($type){
			case "gif":
				if(!imagegif($newimage,$filename)){
					throw new Exception("Unable to save new image.");
				}
				break;
			case "jpg":
				if(!imagejpeg($newimage,$filename,100)){
					throw new Exception("Unable to save new image.");
				}
				break;
			case "bmp":
				if(!image2wbmp($newimage,$filename)){
					throw new Exception("Unable to save new image.");
				}
				break;
			case "png":
				if(!imagepng($newimage,$filename)){
					throw new Exception("Unable to save new image.");
				}
				break;
			default:
				throw new Exception("Incorrect image type.");
				break;
		}
		imagedestroy($newimage);
	}
	
	/**
	 * Calculates the size of the new image
	 * @param string $key
	 * @return array
	 */
	private function _calculateSize($key){
		$file = $this->_output[$key];
		$toolarge = false;
		$toosmall=false;
		$widthlarge = false;
		$heightlarge = false;
		$widthsmall = false;
		$heightsmall = false;
		$newwidth = $this->_oldsize['width'];
		$newheight = $this->_oldsize['height'];
		if($file['maxwidth'] and $this->_oldsize['width'] > $file['maxwidth']){
			//width is too large
			$toolarge = true;
			$widthlarge = true;
			if(!$this->_resize) throw new Exception("The image is too large.");
		}
		if($file['maxheight'] and $this->_oldsize['height'] > $file['maxheight']){
			//height is too large
			$toolarge = true;
			$heightlarge = true;
			if(!$this->_resize) throw new Exception("The image is too large.");
		}
		if($file['minwidth'] and $this->_oldsize['width'] < $file['minwidth']){
			//width is too small
			$toosmall = true;
			$widthsmall = true;
			if(!$this->_resize) throw new Exception("The image is too small.");
		}
		if($file['minheight'] and $this->_oldsize['height'] < $file['minheight']){
			//height is too small
			$toosmall = true;
			$heightsmall = true;
			if(!$this->_resize) throw new Exception("The image is too small.");
		}
		if($this->_keepaspect){
			if($toolarge and $toosmall){
				throw new Exception("Impossible to find correct height and width.");
			}
			if($toolarge){
				if($widthlarge and $heightlarge){
					//Both width and height are too large
					//determine which which one is off by more.
					$widthratio = $this->_oldsize['width'] / $file['maxwidth'];
					$heightratio = $this->_oldsize['height'] / $file['maxheight'];
					$ratio = $this->_oldsize['width'] / $this->_oldsize['height'];
					if($widthratio >= $heightratio){
						//width is bigger
						$newwidth = $file['maxwidth'];
						$newheight = ($this->_oldsize['height'] * $newwidth) / $this->_oldsize['width'];
						//check if height is big enough.
						if($newheight < $file['minheight']){
							throw new Exception("Impossible to find correct height and width.");
						}
					}else{
						//height is bigger
						$newheight = $file['maxheight'];
						$newwidth = ($this->_oldsize['width'] * $newheight) / $this->_oldsize['height'];
						//check if width is big enough.
						if($newwidth < $file['minwidth']){
							throw new Exception("Impossible to find correct height and width.");
						}
					}
				}else{
					if($widthlarge){
						//only width too large
						$widthratio = $this->_oldsize['width'] / $file['maxwidth'];
						$newwidth = $file['maxwidth'];
						$newheight = ($this->_oldsize['height'] * $newwidth) / $this->_oldsize['width'];
						//check if height is big enough.
						if($newheight < $file['minheight']){
							throw new Exception("Impossible to find correct height and width.");
						}
					}elseif($heightlarge){
						//only height too large
						$heightratio = $this->_oldsize['height'] / $file['maxheight'];
						$newheight = $file['maxheight'];
						$newwidth = ($this->_oldsize['width'] * $newheight) / $this->_oldsize['height'];
						//check if width is big enough.
						if($newwidth < $file['minwidth']){
							throw new Exception("Impossible to find correct height and width.");
						}
					}
				}
			}elseif($toosmall){
				if($widthsmall and $heightsmall){
					//Both width and height are too small
					//determine which which one is off by more.
					$widthratio = $file['minwidth'] / $this->_oldsize['width'];
					$heightratio = $file['minheight'] / $this->_oldsize['height'];
					$ratio = $this->_oldsize['width'] / $this->_oldsize['height'];
					if($widthratio <= $heightratio){
						//width is bigger
						$newwidth = $file['minwidth'];
						$newheight = ($this->_oldsize['height'] * $newwidth) / $this->_oldsize['width'];
						//check if height is small enough.
						if($newheight > $file['maxheight']){
							throw new Exception("Impossible to find correct height and width.");
						}
					}else{
						//height is bigger
						$newheight = $file['minheight'];
						$newwidth = ($this->_oldsize['width'] * $newheight) / $this->_oldsize['height'];
						//check if width is small enough.
						if($newwidth > $file['maxwidth']){
							throw new Exception("Impossible to find correct height and width.");
						}
					}
				}else{
					if($widthsmall){
						//only width too small
						$widthratio = $file['minwidth'] / $this->_oldsize['width'];
						$newwidth = $file['minwidth'];
						$newheight = ($this->_oldsize['height'] * $newwidth) / $this->_oldsize['width'];
						//check if height is small enough.
						if($newheight > $file['maxheight']){
							throw new Exception("Impossible to find correct height and width.");
						}
					}
					if($heightsmall){
						//only height too small
						$heightratio = $file['minheight'] / $this->_oldsize['height'];
						$newheight = $file['minheight'];
						$newwidth = ($this->_oldsize['width'] * $newheight) / $this->_oldsize['height'];
						//check if width is small enough.
						if($newwidth > $file['maxwidth']){
							throw new Exception("Impossible to find correct height and width.");
						}
					}
				}
			}
		}else{
			if($widthlarge){
				$newwidth = $file['maxwidth'];
			}
			if($heightlarge){
				$newheight = $file['maxheight'];
			}
			if($widthsmall){
				$newwidth = $file['minwidth'];
			}
			if($heightsmall){
				$newheight = $file['minheight'];
			}
		}
		$this->_newsize[$key] = array(
			"width"  => $newwidth,
			"height" => $newheight
		);
		return $this->_newsize[$key];
	}
	
	/**
	 * Makes sure file is an image.
	 *
	 * @return boolean
	 */
	private function _checkFileType(){
		$types = array(
			"image/gif"           => "gif",
			"image/jpeg"          => "jpg",
			"image/pjpeg"         => "jpg",
			"image/bmp"           => "bmp",
			"image/x-windows-bmp" => "bmp",
			"image/png"           => "png"
		);
		$type = getimagesize($this->_file);
		if(!$type){
			throw new Exception("Provided file is not an image.");
		}
		return $types[image_type_to_mime_type($type[2])];
	}
}
