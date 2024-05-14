<?php

class Storage
{
    private $root = null;
    private $real;
    private $name;
      
    public function __construct()
    {
        $this->root = realpath(__DIR__ . '/../../../../storage');
    }

    private function createDirectory($dir, $permission)
    {
        $umask = umask(0);
        return mkdir($this->root . '/' . $dir, $permission, true);
    }

    private function isIe()
    {
        return (false !== strripos($_SERVER['HTTP_USER_AGENT'], 'MSIE') 
            || (false !==  strripos($_SERVER['HTTP_USER_AGENT'], 'Trident') 
                && false !==  strripos($_SERVER['HTTP_USER_AGENT'], 'rv:11.'))
            ) ? true : false;
    }

    private function taskDownload($real, $name)
    {
        if(!file_exists(($this->real = $this->pathFile($real)))) throw new \ArtisticException('The file could not be found on storage. : '. $real, 500 );
        $this->name = urlencode($this->makeName($name));
    }

    private function pathFile($real)
    {
        return $this->root . '/' . $real;
    }

    private function makeName($name)
    {
        return (false !== $this->isIe()) ? iconv('UTF-8', 'CP949', $name) : $name;
    }

    private function rfDelFile($file)
    {
        return unlink($file);
    }

    private function rfDelDir($dir)
    {
        $handler = (true === is_dir($dir)) ? opendir($dir) : null;

        if (true !== is_resource($handler)) return false;
        while($cursor = readdir($handler)) {
            if ($cursor != '.' && $cursor != '..') {
                $resource = $dir . '/' . $cursor;

                if (is_file($resource)) $this->rfDelFile($resource);
                else $this->rfDelDir($resource);
            }
        }
        closedir($handler);
        if(is_dir($dir)) rmdir($dir);
        return true;
    }

    private function imageType()
    {
        $mimes = config('mime');
        return isset($mimes['image']) ? $mimes['image'] : array('image/gif', 'image/jpeg', 'image/png');
    }

    private function getMime()
    {
        return mime_content_type($this->real);
    }

    private function trimSeparator($dest)
    {
        return trim($dest, DIRECTORY_SEPARATOR);
    }

    public function uniqueName($name)
    {
        return md5($name 
            . uniqid() 
            . microtime() 
            . $_SERVER['REMOTE_ADDR']) 
            . '.' . pathinfo($name, PATHINFO_EXTENSION);
    }

    public function isDir($dir)
    {
        return is_dir($this->root . '/' . $this->trimSeparator($dir));
    }

    public function removeEmpty(array $item)
    {
        return array_filter($item, function($item) {
            return $item['error'] === 0;
            });
    }

    public function makeDirectory($dir, $permission = 0755)
    {
        if (strlen($dir) < 1) return false;
        if (false !== $this->isDir($dir)) return false;
        return $this->createDirectory($this->trimSeparator($dir), $permission);
    }

    public function download($real, $name = '')
    {
        if (strlen($real) < 1) throw new \ArtisicException('No input real file name');
        $this->taskDownload($real, $name);
        $mime = $this->getMime();

        ob_start();
        header('Content-Type: '. $mime);
        header('Content-Disposition: attachment; filename= "' . $this->name . '"');
        header('Content-Transfer-Encoding: Binary');
        ob_clean();
        flush();
        return readfile($this->real);
    }

    public function save($tmp_name, $dest)
    {
        $directory = dirname(str_replace('.', '', $dest));
        $savename = basename($dest);

        if ($directory != '.' && false === $this->isDir($directory)) $this->makeDirectory($directory);
        $directory = ($directory == '.') ? '' : $directory;

        if (!file_exists($tmp_name)) throw new \ArtisticException('The file does not exist.', 500);

        if (!is_uploaded_file($tmp_name)) return rename($tmp_name, $this->root . '/' . $directory . '/' . $savename);
        else return move_uploaded_file($tmp_name, $this->root . '/' . $directory . '/' . $savename);
    }

    public function viewImage($dest)
    {
        $dest  = $this->root . '/' . $this->trimSeparator($dest);
        $mime   = mime_content_type($dest);
        $types  = $this->imageType();
        if (!in_array($mime, $types)) return false;
        header('Content-Type: '. $mime);
        echo file_get_contents($dest);
    }

    public function deleteDirectory($dest)
    {
        return $this->rfDelDir($this->root . '/' . $this->trimSeparator($dest));
    }

    public function deleteFile($dest)
    {
        return $this->rfDelFile($this->root . '/' . $this->trimSeparator($dest));
    }

    public function move($origin, $move)
    {
        $origin = $this->root . '/' . $this->trimSeparator($origin);
        $move   = $this->root . '/' . $this->trimSeparator($move);

       if(file_exists($origin)) rename($origin, $move);
    }

    public function isFile($dest)
    {
        return file_exists($this->root . '/' . $this->trimSeparator($dest));
    }

    public function imageInfo($dest)
    {
        $dest = $this->root . '/' .$this->trimSeparator($dest);

        return file_exists($dest) ? getimagesize($dest) : false;
    }

}//end class
