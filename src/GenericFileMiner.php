<?php

namespace Ideil\LaravelGenericFile;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Debug\ExceptionHandler as SymfonyDisplayer;
use Intervention\Image\ImageManagerStatic as Image;
use InvalidArgumentException;
use Exception;

class GenericFileMiner
{
    use Traits\TokenTrait;

    /**
     * @var string
     */
    protected $request_checksum = null;

    /**
     * @var string
     */
    protected $request_file_path = null;

    /**
     * @var string
     */
    protected $clear_uri = null;

    /**
     * @var array
     */
    protected $thumb_handlers = [];

    /**
     * @var Request
     */
    protected $request = null;

    /**
     * @var string
     */
    protected $uri_root = '';

    /**
     * @var string
     */
    protected $handled_files_root = '';

    /**
     * @var string
     */
    protected $original_files_root = '';

    /**
     * @var string
     */
    protected $path_regexp;

    /**
     * @var bool
     */
    protected $is_debug = false;

    /**
     * @param bool $is_active
     */
    public function setDevModeActivity($is_active)
    {
        $this->is_debug = $is_active;
    }

    /**
     * @param string $url
     *
     * @return Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function getRedirectTo($url)
    {
        return new RedirectResponse($url);
    }

    /**
     * @param string   $pattern
     * @param callable $handler
     *
     * @return mixed
     */
    public function uriMatch($pattern, callable $handler)
    {
        if (preg_match($pattern, $this->getCleanUri(), $matches)) {
            return $handler($this->getCleanUri(), $matches);
        }
    }

    /**
     * @param string   $pattern
     * @param callable $handler
     *
     * @return mixed
     */
    public function uriNotMatch($pattern, callable $handler)
    {
        if (!preg_match($pattern, $this->getCleanUri(), $matches)) {
            return $handler($this->getCleanUri(), $matches);
        }
    }

    /**
     * @param string   $name
     * @param string   $regexp
     * @param callable $handler
     */
    public function addThumbHandler($name, $regexp, callable $handler)
    {
        $this->thumb_handlers[$name] = [$regexp, $handler];
    }

    /**
     */
    public function handle()
    {
        try {
            if ($this->getRequestChecksum()) {
                $uri_root = rtrim(str_replace('{checksum}',
                    $this->getRequestChecksum(), $this->uri_root), '/');

                // with trailling left slash

                if (strpos($this->getCleanUri(), $uri_root) !== 0) {
                    throw new InvalidArgumentException('Wrong uri root');
                }

                $uri_payload = substr($this->getCleanUri(), strlen($uri_root));

                if ($this->getRequestChecksum() !== $this->token6FromStr($uri_payload)) {
                    throw new InvalidArgumentException('Invalid checksum value');
                }
            }

            if (!file_exists($real_file_path = rtrim($this->original_files_root, '/').'/'.ltrim($this->getRequestFilePath(), '/'))) {
                throw new InvalidArgumentException('File not exists '.$real_file_path);
            }

            Image::configure(
                config('generic-file.miner.imageHandling')
            );

            $image = Image::make($real_file_path);

            foreach ($this->thumb_handlers as $handler_name => $handler_data) {
                if (preg_match($handler_data[0], $uri_payload, $matches)) {
                    $handler_data[1]($image, $matches);
                }
            }

            $this->save($image);

            $response = new Response($image->response(), Response::HTTP_OK, [
                'content-type' => $image->mime(),
                'content-length' => $image->filesize(),
                'cache-control' => 'max-age=315360000',
                'cache-control' => 'public',
                'accept-ranges' => 'bytes',
            ]);

            return $response->prepare($this->request);
        } catch (Exception $e) {
            if ($this->is_debug) {
                return (new SymfonyDisplayer($this->is_debug))->createResponse($e);
            }

            return new Response('404 Not found', Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @param Image $image
     */
    public function save($image)
    {
        if ($this->handled_files_root) {
            $file_store_path = rtrim($this->handled_files_root, '/').'/'.ltrim($this->getCleanUri(), '/');

            $file_store_dir = dirname($file_store_path);

            if (!file_exists($file_store_dir)) {
                mkdir($file_store_dir, 0755, true);
            }

            $image->save($file_store_path);
        }
    }

    /**
     * @return array
     */
    public function getRequestChecksum()
    {
        if (!is_null($this->request_checksum)) {
            return $this->request_checksum;
        }

        $parts = explode('{checksum}', $this->uri_root, 2);

        if (count($parts) === 2) {
            if (!preg_match('~'.implode('([a-z\d]+)', $parts).'~', $this->getCleanUri(), $matches)) {
                throw new InvalidArgumentException('Invalid checksum format');
            }

            return $this->request_checksum = $matches[1];
        }

        return $this->request_checksum = false;
    }

    /**
     */
    public function getCleanUri()
    {
        if (!is_null($this->clear_uri)) {
            return $this->clear_uri;
        }

        list($this->clear_uri) = explode('?', $this->request->getRequestUri());

        return $this->clear_uri;
    }

    /**
     */
    public function getRequestFilePath()
    {
        if (!is_null($this->request_file_path)) {
            return $this->request_file_path;
        }

        if (!preg_match($this->path_regexp, $this->getCleanUri(), $matches)) {
            throw new InvalidArgumentException('Invalid file path format');
        }

        return $this->request_file_path = $matches[0];
    }

    /**
     * @param string $path
     */
    public function setHandledFilesRoot($path)
    {
        $this->handled_files_root = $path;
    }

    /**
     * @param string $path
     */
    public function setOriginalFilesRoot($path)
    {
        $this->original_files_root = $path;
    }

    /**
     * @param string $path
     */
    public function setUriRoot($path)
    {
        $this->uri_root = $path;
    }

    /**
     * @param Request $request
     * @param string  $uri_root
     */
    public function __construct(Request $request, $path_regexp)
    {
        $this->request = $request;

        $this->path_regexp = $path_regexp;
    }
}
