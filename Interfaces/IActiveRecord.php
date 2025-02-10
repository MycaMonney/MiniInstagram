<?php
namespace Interfaces;

interface IActiveRecord
{
    public static function find($id);
    public function create();
    public function update();
    public function delete();
}