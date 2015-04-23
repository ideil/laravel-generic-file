<?php namespace Ideil\LaravelGenericFile\Traits;

use Symfony\Component\HttpFoundation\File\UploadedFile;

trait EloquentTrait
{
	/**
	 * @var Ideil\LaravelGenericFile\GenericFile
	 */
	protected static $generic_file;

	/**
	 * @return null
	 */
	public static function bootEloquentTrait()
	{
		self::$generic_file = app('generic-file');
	}

	/**
	 * Store uploaded file by configured path pattern and fill data to this model
	 *
	 * @param  Symfony\Component\HttpFoundation\File\UploadedFile $file
	 * @return Illuminate\Database\Eloquent\Model
	 */
	public function upload(UploadedFile $file)
	{
		self::$generic_file->moveUploadedFile($file, null, $this);

		return $this;
	}

	/**
	 * Make url to file joined to this model using configured path pattern
	 *
	 * @return string
	 */
	public function url()
	{
		return self::$generic_file->makeUrlToUploadedFile($this);
	}

	/**
	 * Make full path to file joined to this model using configured path pattern
	 *
	 * @return string
	 */
	public function path()
	{
		return self::$generic_file->makePathToUploadedFile($this);
	}

	/**
	 * Delete file if not use in other models
	 *
	 * @return string
	 */
	public function delete()
	{
		if (self::$generic_file->canRemoveFiles())
		{
			$file_usage_count = 0;

			if (method_exists($this, 'getFileUsageCount'))
			{
				$file_usage_count = $this->getFileUsageCount($this);
			}

			if ($file_usage_count <= 1)
			{
				self::$generic_file->delete($this);
			}
		}

		return parent::delete();
	}

	/**
	 * Return default file assign map
	 *
	 * @return array
	 */
	public function getFileAssignMap()
	{
		return [
			'contentHash' => 'filename',
		];
	}

	/**
	 * Before upload event, cancel file store if false returned
	 *
	 * @return boolean
	 */
	public function beforeUpload(UploadedFile $file)
	{
		return true;
	}

}
