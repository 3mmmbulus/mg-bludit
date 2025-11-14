<?php defined('MAIGEWAN') or die('Maigewan CMS.');

require_once PATH_HELPERS . 'imagelibrary.class.php';

class LogoLibrary extends ImageLibrary
{
    protected $configDirectoryName = 'logo-library';
    protected $translationPrefix = 'logo-library';
}
