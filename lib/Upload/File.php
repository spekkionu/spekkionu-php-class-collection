<?php
/**
 * File Upload Class
 *
 * @author Jonathan Bernardi
 * @copyright 2008
 * @version 1.1 5/28/2008
 * @package Upload
 * @license MIT License
 */
class Upload_File {
  /**
   * @var string $_file The file to upload.
   */
  private $_file;
  /**
   * @var string $_dir The directory to upload the file to.
   */
  private $_dir;
  /**
   * @var string $_oldfilename The desired filename.
   */
  private $_oldfilename;
  /**
   * @var string $_newfilename The final saved filename.
   */
  private $_newfilename;
  /**
   * @var string $_overmethod What to do if file already exists in upload directory.
   */
  private $_overmethod = "over";
  /**
   * @var string|array $_extensions An extension or array of extensions that are acceptable.
   */
  private $_extensions = array();
  /**
   * @var string $_fileext The file extension.
   */
  private $_fileext;
  /**
   * @var string $_maxsize The max filesize allowed.
   */
  private $_maxsize;
  /**
   * @var string $_uffixtype Whether to append the uffix to beginning or end of filename.
   */
  private $_uffixtype = "";
  /**
   * @var string $_uffix The string to append to the filename.
   */
  private $_uffix = "";
  /**
   * Class Constructor
   * @access public
   * @param string $file A pointer to the uploaded file.  Can be absolute or relative path or from the $_FILES superglobal
   * @param string $dir The directory to upload the file to. Can be absolute or relative path.
   * @param string $filename The desired filename.
   * @return Upload_File
   */
  public function __construct($file, $dir, $filename) {
    $this->_setFile($file);
    $this->_setFileName($filename);
    $this->_setUploadPath($dir);
    $this->_maxsize = 1000000 * intval(ini_get('upload_max_filesize'));
  }
  /**
   * Sets the path to to the upload directory.
   * @access private
   * @param string $dir The desired upload directory
   */
  private function _setUploadPath($dir) {
    $dir = trim($dir);
    if (!is_dir($dir)) {
      throw new Exception("Upload Path doesn't exist.");
    }
    if (!is_writable($dir)) {
      throw new Exception("Upload Path isn't writable.");
    }
    if (substr($dir, -1) == "/") $dir = rtrim($dir, "/");
    $this->_dir = $dir;
  }
  /**
   * Sets the desired filename
   * @access private
   * @param string $name The desired filename.
   */
  private function _setFileName($name) {
    $name = basename(trim($name));
    $name = str_replace(" ", "_", $name);
    $this->_oldfilename = $name;
    $name = explode(".", $name);
    if (count($name) <= 1) {
      throw new Exception("Filename has no extension.");
    }
    $this->_fileext = strtolower(trim(array_pop($name)));
  }
  /**
   * Sets the file resource
   * @access private
   * @param string $file The file resource.
   */
  private function _setFile($file) {
    if (!is_uploaded_file($file)) {
      if (!is_file($file)) {
        throw new Exception("Not a valid file.");
      }
    }
    $this->_file = $file;
  }
  /**
   * Sets allowed extensions for uploaded file.
   * @access public
   * @param string|array $ext An acceptable extension or array of accepted extensions.
   * @return Upload_File
   */
  public function setExtensions($ext) {
    if (!is_array($ext)) $ext = explode("|", $ext);
    foreach($ext as $value) {
      $value = trim($value);
      $value = str_replace(array("*", "."), "", $value);
      $this->_extensions[] = $value;
    }
    return $this;
  }
  /**
   * Checks the file extension against list of acceptable extensions.
   * If Upload_File::setExtensions() is not called will always return true
   * @access private
   * @return boolean
   */
  private function _checkExtensions() {
    if (count($this->_extensions) == 0) {
      return true;
    } else {
      return in_array($this->_fileext, $this->_extensions);
    }
  }
  /**
   * Sets the max filesize.
   * If larger than the php upload_max_filesize the php.ini setting will be used instead.
   * @access public
   * @return Upload_File
   */
  public function setMaxSize($size) {
    $this->_maxsize = min($size, 1000000 * intval(ini_get('upload_max_filesize')));
    return $this;
  }
  /**
   * Sets an uffix to append to the beginning or end of the filename.
   * @access public
   * @param string $uffix The uffic to append.
   * @param string $type Can be "pre" or "suff" This tells whether to append to the beginning or end of the filename.
   * @return Upload_File
   */
  public function setUffix($uffix, $type = "pre") {
    $uffix = trim($uffix);
    $type = trim($type);
    switch ($type) {
      case "pre":
        $this->_uffixtype = "pre";
      break;
      case "suff":
        $this->_uffixtype = "suff";
      break;
      default:
        throw new Exception("Not a valid uffix type.");
      break;
    }
    $this->_uffix = $uffix;
    return $this;
  }
  /**
   * Sets what to do if the uploaded file already exists in the upload directory.
   * @access public
   * @param striong $type Can be "overwrite", "unique", or "error".
   *   "overwrite" will overwrite the existing file.
   *   "unique" will append _n to the uploaded filename until it is unique.
   *   "error" will throw an exception of the file already exists.
   * @return Upload_File
   */
  public function setIfExists($type = "over") {
    $type = trim($type);
    switch ($type) {
      case "overwrite":
      case "over":
        $this->_overmethod = "over";
      break;
      case "unique":
        $this->_overmethod = "unique";
      break;
      case "error":
        $this->_overmethod = "error";
      break;
      default:
        throw new Exception("Not a valid duplicate file method.");
      break;
    }
    return $this;
  }
  /**
   * Performs the file upload.
   * Returns the new filename.
   * @access public
   * @return string The new filename.
   */
  public function performUpload() {
    $this->_newfilename = $this->_checkFileName();
    if (!$this->_checkExtensions()) {
      throw new Exception("Not an acceptable extension.");
    }
    if (is_uploaded_file($this->_file)) {
      if (move_uploaded_file($this->_file, $this->_dir . "/" . $this->_newfilename)) {
        return $this->_newfilename;
      } else {
        throw new Exception("Error saving file.");
      }
    } else {
      if (rename($this->_file, $this->_dir . "/" . $this->_newfilename)) {
        return $this->_newfilename;
      } else {
        throw new Exception("Error saving file.");
      }
    }
  }
  /**
   * Checks the filename to make sure it doesn't exists.
   * If it does exist $Upload_File::_overmethod varibable determines action.
   * @access private
   * @return string New filename.
   */
  private function _checkFileName() {
    $filename = explode(".", $this->_oldfilename);
    $ext = $this->_fileext;
    array_pop($filename);
    $oldname = implode(".", $filename);
    if ($this->_uffix != "") {
      if ($this->_uffixtype == "pre") {
        $oldname = $this->_uffix . $oldname;
      } elseif ($this->_uffixtype == "suff") {
        $oldname = $oldname . $this->_uffix;
      }
    }
    $name = $oldname . "." . $ext;
    if (!file_exists($this->_dir . "/" . $name)) {
      //If it doesn't return the filename.
      return $name;
    } else {
      switch ($this->_overmethod) {
        case "over":
          return $name;
        break;
        case "unique":
          //Create a unique filename.
          $j = 1;
          do {
            $fn = $oldname . "_" . $j;
            $j++;
          }
          while (file_exists($this->_dir . "/" . $fn . "." . $ext));
          return $fn . "." . $ext;
          break;
        case "error":
          throw new Exception("File already exists and duplicate method set to error.");
          break;
        default:
          throw new Exception("Incorrect overwrite method.");
          break;
        }
    }
  }
}
