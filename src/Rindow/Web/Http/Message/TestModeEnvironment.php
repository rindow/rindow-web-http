<?php
namespace Rindow\Web\Http\Message;

use Rindow\Stdlib\Entity\AbstractEntity;

class TestModeEnvironment extends AbstractEntity
{
    public $_SERVER;
    public $_GET;
    public $_POST;
    public $_FILES;
    public $_COOKIE;
    public $input;
}
