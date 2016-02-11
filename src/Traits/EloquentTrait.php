<?php

namespace Ideil\LaravelGenericFile\Traits;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Ideil\GenericFile\Resources\File;
use Input;

trait EloquentTrait
{
    /**
     * @var Ideil\LaravelGenericFile\GenericFile
     */
    protected static $generic_file;

    /**
     */
    public static function bootEloquentTrait()
    {
        self::$generic_file = app('generic-file');
    }

    /**
     * Store uploaded file by configured path pattern and fill data to this model.
     *
     * @param Symfony\Component\HttpFoundation\File\UploadedFile $file
     *
     * @return Illuminate\Database\Eloquent\Model
     */
    public function upload(UploadedFile $file)
    {
        self::$generic_file->moveUploadedFile(new File($file,
            $file->getClientOriginalName()), null, $this);

        return $this;
    }

    /**
     * Make url to file joined to this model using configured path pattern.
     *
     * @return string
     */
    public function url()
    {
        return self::$generic_file->makeUrlToUploadedFile($this);
    }

    /**
     * Make full path to file joined to this model using configured path pattern.
     *
     * @return string
     */
    public function path()
    {
        return self::$generic_file->makePathToUploadedFile($this);
    }

    /**
     * Delete file if not use in other models.
     *
     * @return string
     */
    public function delete()
    {
        if (self::$generic_file->canRemoveFiles()) {
            $file_usage_count = 0;

            if (method_exists($this, 'getFileUsageCount')) {
                $file_usage_count = $this->getFileUsageCount($this);
            }

            if ($file_usage_count <= 1) {
                self::$generic_file->delete($this);
            }
        }

        return parent::delete();
    }

    /**
     * Return default file assign map.
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
     * @param string $url
     *
     * @return Model
     */
    public static function createFromUrl($url)
    {
        $instance = new static();

        if (self::$generic_file->fetchUrl($url, null, $instance)) {
            return $instance;
        }

        return;
    }

    /**
     * @param string $name
     *
     * @return Model
     */
    public static function createFromInput($name)
    {
        return self::createFromUploadedFile(Input::file($name));
    }

    /**
     * @param string $name
     *
     * @return Model
     */
    public static function createFromUploadedFile(UploadedFile $file = null)
    {
        if (!$file instanceof UploadedFile) {
            return;
        }

        $instance = new static();

        self::$generic_file->moveUploadedFile(new File($file,
            $file->getClientOriginalName()), null, $instance);

        return $instance;
    }

    /**
     * Before upload event, cancel file store if false returned.
     *
     * @return bool
     */
    public function beforeUpload(File $file)
    {
        return true;
    }
}
