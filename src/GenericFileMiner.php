<?php namespace Ideil\LaravelGenericFile;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Intervention\Image\ImageManagerStatic as Image;

use InvalidArgumentException;

class GenericFileMiner {

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
	 * @param string   $name
	 * @param string   $regexp
	 * @param Callable $handler
	 */
	public function addThumbHandler($name, $regexp, Callable $handler)
	{
		$this->thumb_handlers[$name] = [$regexp, $handler];
	}

	/**
	 * @param  InvalidArgumentException $e
	 * @return void
	 */
	public function invalidArgumentResponse($e)
	{
		return new Response('404 Not found', Response::HTTP_NOT_FOUND);
	}

	/**
	 * @return void
	 */
	public function handle()
	{
		try
		{
			if ($this->getRequestChecksum())
			{
				$uri_root     = rtrim(str_replace('{checksum}',
					$this->getRequestChecksum(), $this->uri_root), '/');

				// with trailling left slash

				$hashable_uri = substr($this->getCleanUri(), strlen($uri_root));

				if ($this->getRequestChecksum() !== $this->token6FormStr($hashable_uri))
				{
					throw new InvalidArgumentException('Invalid checksum value');
				}
			}

			if ( ! file_exists($real_file_path = rtrim($this->original_files_root, '/') . '/' . ltrim($this->getRequestFilePath(), '/')))
			{
				throw new InvalidArgumentException('File not exists ' . $real_file_path);
			}

			Image::configure(['driver' => 'imagick']);

			$image = Image::make($real_file_path);

			foreach ($this->thumb_handlers as $handler_name => $handler_data)
			{
				if (preg_match($handler_data[0], $this->getCleanUri(), $matches))
				{
					$handler_data[1]($image, $matches);
				}
			}

			$this->save($image);

			$response = new Response($image->response(), Response::HTTP_OK, [
				'content-type'   => $image->mime(),
				'content-length' => $image->filesize(),
				'cache-control'  => 'max-age=315360000',
				'cache-control'  => 'public',
				'accept-ranges'  => 'bytes',
			]);

			return $response->prepare($this->request);

		}
		catch (InvalidArgumentException $e)
		{
			return $this->invalidArgumentResponse($e);
		}
	}

	/**
	 * @param  Image $image
	 * @return void
	 */
	public function save($image)
	{
		if ($this->handled_files_root)
		{
			$file_store_path = rtrim($this->handled_files_root, '/') . '/' . ltrim($this->getCleanUri(), '/');

			$file_store_dir  = dirname($file_store_path);

			if ( ! file_exists($file_store_dir))
			{
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
		if ( ! is_null($this->request_checksum))
		{
			return $this->request_checksum;
		}

		$parts = explode('{checksum}', $this->uri_root, 2);

		if (count($parts) === 2)
		{
			if ( ! preg_match('~' . implode('([a-z\d]+)', $parts) . '~', $this->getCleanUri(), $matches))
			{
				throw new InvalidArgumentException('Invalid checksum format');
			}

			return $this->request_checksum = $matches[1];
		}

		return $this->request_checksum = false;
	}

	/**
	 * @return void
	 */
	public function getCleanUri()
	{
		if ( ! is_null($this->clear_uri))
		{
			return $this->clear_uri;
		}

		list($this->clear_uri) = explode('?', $this->request->getRequestUri());

		return $this->clear_uri;
	}

	/**
	 * @return void
	 */
	public function getRequestFilePath()
	{
		if ( ! is_null($this->request_file_path))
		{
			return $this->request_file_path;
		}

		if ( ! preg_match($this->path_regexp, $this->getCleanUri(), $matches))
		{
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
	 * @param  Request $request
	 * @param  string $uri_root
	 * @return void
	 */
	public function __construct(Request $request, $path_regexp)
	{
		$this->request = $request;

		$this->path_regexp = $path_regexp;
	}

}
