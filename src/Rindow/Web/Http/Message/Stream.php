<?php
namespace Rindow\Web\Http\Message;

use Psr\Http\Message\StreamInterface;
use Rindow\Web\Http\Exception;

/**
 * Describes a data stream.
 *
 * Typically, an instance will wrap a PHP stream; this interface provides
 * a wrapper around the most common operations, including serialization of
 * the entire stream to a string.
 */
class Stream implements StreamInterface
{
    protected $resource;

    public function __construct($resource=null)
    {
        if($resource)
            $this->attach($resource);
    }

    /**
     * Reads all data from the stream into a string, from the beginning to end.
     *
     * This method MUST attempt to seek to the beginning of the stream before
     * reading data and read the stream until the end is reached.
     *
     * Warning: This could attempt to load a large amount of data into memory.
     *
     * This method MUST NOT raise an exception in order to conform with PHP's
     * string casting operations.
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
     * @return string
     */
    public function __toString()
    {
        $this->rewind();
        return $this->getContents();
    }

    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close()
    {
        if($this->resource==null)
            return;
        fclose($this->resource);
    }

    public function attach($resource)
    {
        if(!is_resource($resource))
            throw new Exception\InvalidArgumentException('Invalid stream resource type.');
        $this->resource = $resource;
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return resource|null Underlying PHP stream, if any
     */
    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;
        return $resource;
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize()
    {
        if($this->resource==null)
            return null;
        $stats = fstat($this->resource);
        $size = isset($stats['size']) ? $stats['size'] : null;
        return $size;
    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int Position of the file pointer
     * @throws \RuntimeException on error.
     */
    public function tell()
    {
        if($this->resource==null)
            throw new Exception\RuntimeException('no resource available');
        $offset = ftell($this->resource);
        if($offset===false)
            throw new Exception\RuntimeException('seek pointer error');
        return $offset;
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof()
    {
        if($this->resource==null)
            return true;
        return feof($this->resource);
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable()
    {
        if($this->resource==null)
            return false;
        $metadata = stream_get_meta_data($this->resource);
        return isset($metadata['seekable']) && $metadata['seekable'];
    }

    /**
     * Seek to a position in the stream.
     *
     * @link http://www.php.net/manual/en/function.fseek.php
     * @param int $offset Stream offset
     * @param int $whence Specifies how the cursor position will be calculated
     *     based on the seek offset. Valid values are identical to the built-in
     *     PHP $whence values for `fseek()`.  SEEK_SET: Set position equal to
     *     offset bytes SEEK_CUR: Set position to current location plus offset
     *     SEEK_END: Set position to end-of-stream plus offset.
     * @throws \RuntimeException on failure.
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if($this->resource==null)
            throw new Exception\RuntimeException('no resource available');
        if(!$this->isSeekable())
            throw new Exception\RuntimeException('Stream is not seekable');
        if(fseek($this->resource, $offset, $whence) === -1)
            throw new Exception\RuntimeException('seek error');
    }

    /**
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will raise an exception;
     * otherwise, it will perform a seek(0).
     *
     * @see seek()
     * @link http://www.php.net/manual/en/function.fseek.php
     * @throws \RuntimeException on failure.
     */
    public function rewind()
    {
        if($this->resource==null)
            throw new Exception\RuntimeException('no resource available');
        if(rewind($this->resource) === false)
            throw new Exception\RuntimeException('rewind error');
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        if($this->resource==null)
            return false;
        $metadata = stream_get_meta_data($this->resource);
        $mode = $metadata['mode'];
        if(strpos($mode, 'w')===false &&
            strpos($mode, 'a')===false &&
            strpos($mode, 'c')===false &&
            strpos($mode, 'x')===false &&
            strpos($mode, '+')===false)
            return false;
        return true;
    }

    /**
     * Write data to the stream.
     *
     * @param string $string The string that is to be written.
     * @return int Returns the number of bytes written to the stream.
     * @throws \RuntimeException on failure.
     */
    public function write($string)
    {
        if($this->resource==null)
            throw new Exception\RuntimeException('no resource available');
        if(!$this->isWritable())
            throw new Exception\RuntimeException('Stream is not writable');
        $len = fwrite($this->resource, $string);
        if($len===false)
            throw new Exception\RuntimeException('write error');
        return $len;
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        if($this->resource==null)
            return false;
        $metadata = stream_get_meta_data($this->resource);
        $mode = $metadata['mode'];
        if(strpos($mode, 'r')===false &&
            strpos($mode, '+')===false)
            return false;
        return true;
    }

    /**
     * Read data from the stream.
     *
     * @param int $length Read up to $length bytes from the object and return
     *     them. Fewer than $length bytes may be returned if underlying stream
     *     call returns fewer bytes.
     * @return string Returns the data read from the stream, or an empty string
     *     if no bytes are available.
     * @throws \RuntimeException if an error occurs.
     */
    public function read($length)
    {
        if($this->resource==null)
            throw new Exception\RuntimeException('no resource available');
        if(!$this->isReadable())
            throw new Exception\RuntimeException('Stream is not readable');
        $data = fread($this->resource, $length);
        if($data===false)
            throw new Exception\RuntimeException('read error');
        return $data;
    }

    /**
     * Returns the remaining contents in a string
     *
     * @return string
     * @throws \RuntimeException if unable to read or an error occurs while
     *     reading.
     */
    public function getContents()
    {
        if($this->resource==null)
            throw new Exception\RuntimeException('no resource available');
        if(!$this->isReadable())
            throw new Exception\RuntimeException('Stream is not readable');
        if($this->isSeekable())
            $this->rewind();
        $data = stream_get_contents($this->resource);
        if($data===false)
            throw new Exception\RuntimeException('get contents error');
        return $data;
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @link http://php.net/manual/en/function.stream-get-meta-data.php
     * @param string $key Specific metadata to retrieve.
     * @return array|mixed|null Returns an associative array if no key is
     *     provided. Returns a specific key value if a key is provided and the
     *     value is found, or null if the key is not found.
     */
    public function getMetadata($key = null)
    {
        if($this->resource==null)
            return null;
        $metadata = stream_get_meta_data($this->resource);
        if($key==null)
            return $metadata;
        return isset($metadata[$key]) ? $metadata[$key] : null;
    }
}
