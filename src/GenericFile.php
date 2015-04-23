<?php namespace Ideil\LaravelGenericFile;

use Illuminate\Database\Eloquent\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use Ideil\GenericFile\GenericFile as BaseGenericFile;

class GenericFile extends BaseGenericFile
{

	/**
	 * Save file attributes required for interpolation to model
	 *
	 * @param string|Illuminate\Database\Eloquent\Model $model
	 * @param Ideil\LaravelFileOre\Interpolator\InterpolatorResult $input
	 *
	 * @return Illuminate\Database\Eloquent\Model
	 */
	protected function saveModelData(Model $model, \Ideil\GenericFile\Interpolator\InterpolatorResult $input)
	{
		// prepare data to fill model

		$model_map    = $model->getFileAssignMap();
		$model_fields = [];

		foreach ($input->getData() as $field => $value)
		{
			$model_fields[isset($model_map[$field]) ? $model_map[$field] : $field] = $value;
		}

		// not save, just fill data

		$model->fill($model_fields);

		return $model;
	}

	/**
	 * Move uploaded file to path by pattern and update model
	 *
	 * @param Symfony\Component\HttpFoundation\File\UploadedFile $file
	 * @param string|null $path_pattern
	 * @param Illuminate\Database\Eloquent\Model|null|false $existing_model
	 *
	 * @return Illuminate\Database\Eloquent\Model|string|null
	 */
	public function moveUploadedFile(UploadedFile $file, $path_pattern = null, $existing_model = null)
	{
		// get model instance if available
		// not use models if $existing_model === false

		$model_class = $this->getConfig('store.model');

		if (($model_class || $existing_model) && $existing_model !== false)
		{
			$model_instance = $existing_model ?: new $model_class;

			// cancel upload if event returned false

			if ( ! $model_instance->beforeUpload($file))
				return null;
		}

		$interpolated = parent::moveUploadedFile($file, $path_pattern);

		// check is model available
		// and update it

		if (isset($model_instance))
		{
			return $this->saveModelData($model_instance, $interpolated);
		}

		return $interpolated;
	}

	/**
	 * Make url to stored file
	 *
	 * @param array|Illuminate\Database\Eloquent\Model $model
	 * @param string|null $path_pattern
	 *
	 * @return string
	 */
	public function makeUrlToUploadedFile($model, $path_pattern = null, array $model_map = array())
	{
		return parent::makeUrlToUploadedFile($model, $path_pattern,
			$model instanceof Model ? $model->getFileAssignMap() : $model_map);
	}

	/**
	 * Full path to stored file
	 *
	 * @param array|Illuminate\Database\Eloquent\Model $model
	 * @param string|null $path_pattern
	 *
	 * @return string
	 */
	public function makePathToUploadedFile($model, $path_pattern = null, array $model_map = array())
	{
		return parent::makePathToUploadedFile($model, $path_pattern,
			$model instanceof Model ? $model->getFileAssignMap() : $model_map);
	}

}
