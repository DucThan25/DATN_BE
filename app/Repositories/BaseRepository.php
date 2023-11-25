<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Log;

abstract class BaseRepository
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * EloquentRepository constructor.
     */
    public function __construct()
    {
        $this->setModel();
    }

    /**
     * get model
     * @return string
     */
    abstract public function getModel();

    /**
     * Set model
     */
    public function setModel()
    {
        $this->model = app()->make($this->getModel());
    }

    /**
     * Get All
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAll()
    {

        return $this->model->all();
    }

    public function find($id)
    {
        $model = $this->model->findOrFail($id);
        return $model;
    }
    /**
     * Create
     * @param array $attributes
     * @return mixed
     */
    public function create(array $input)
    {
        $model = new $this->model();
        $model->fill($input);
        $model->save();
        return $model;
    }

    /**
     * Update a entity in repository by id
     *
     * @param  array  $input
     * @param $id
     *
     * @return BaseRepository
     */
    public function update(array $input, $id)
    {
        $model = $this->model->findOrFail($id);
        $model->fill($input);
        $model->save();

        return $model;
    }

    /**
     * Delete
     *
     * @param $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->model->destroy($id);
    }

}
