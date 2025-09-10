<?php

namespace app\admin\controller;

class HomeController extends AuthController
{
    public function index()
    {
        return $this->fetch();
    }

    public function uploadSummernoteImg()
    {
        $img_url = upload_file('img_url');

        $imgArr = explode("/", $img_url);
        $source_name = end($imgArr);

        return out(['img_url' => $img_url, 'filename' => $source_name]);
    }
}
