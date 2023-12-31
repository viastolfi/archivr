<?php

class AssetImported extends Element implements JsonSerializable
{
    private string $path;

    private string $model;

    private float $scale;

    private bool $animate = false;

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    public function getScale():string
    {
        return $this->scale . " " . $this->scale . " " . $this->scale;
    }

    public function setScale($scale):void
    {
        $this->scale = $scale;
    }

    public function getScaleInt()
    {
        return $this->scale;
    }
    public function getPath():string
    {
        return $this->path;
    }

    public function getAnimate():bool
    {
        return $this->animate;
    }

    public function setAnimate(bool $animate)
    {
        $this->animate = $animate;
    }

    public function __construct($path, $model)
    {
        parent::__construct($path);
        $this->path = $path;
        $this->model = $model;
        $this->scale = 1;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    public function set($data)
    {
        $this->scale = $data['scale'];
        parent::set($data);
    }
}
